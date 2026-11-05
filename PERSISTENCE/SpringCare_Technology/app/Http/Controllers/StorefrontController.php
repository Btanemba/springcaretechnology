<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliverySchedule;
use App\Models\Order;
use App\Mail\OrderPlaced;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Support\AcademyAdvertisement;
use App\Support\Naira;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class StorefrontController extends Controller
{
    private function products(): Builder
    {
        return Product::query()
            ->where('status', 'published')
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('brand', fn (Builder $query) => $query->where('is_active', true));
    }

    public function home(AcademyAdvertisement $advertisement): View
    {
        $products = $this->products()
            ->whereHas('units', fn (Builder $query) => $query->where('status', 'available'))
            ->with(['brand', 'category', 'units' => fn ($query) => $query->where('status', 'available')->orderBy('selling_price')])
            ->latest()->take(4)->get();

        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $deliverySchedules = DeliverySchedule::query()->where('is_published', true)
            ->orderBy('year')->orderBy('month')->orderBy('city')->get();

        $showAcademyAdvertisement = $advertisement->isVisible();

        return $this->render('storefront.home', compact('products', 'categories', 'deliverySchedules', 'showAcademyAdvertisement'));
    }

    public function shop(Request $request): View
    {
        foreach (['min_price', 'max_price'] as $priceFilter) {
            $request->merge([$priceFilter => Naira::normalizeInput($request->input($priceFilter))]);
        }

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'condition' => ['nullable', Rule::in(array_keys(ProductUnit::CONDITIONS))],
            'storage' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', ...($request->filled('min_price') ? ['gte:min_price'] : [])],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'name'])],
        ]);

        $query = ProductUnit::query()
            ->where('product_units.status', 'available')
            ->whereHas('product', fn (Builder $query) => $query->whereIn(
                'products.id',
                $this->products()->select('products.id'),
            ))
            ->with(['product.brand', 'product.category']);

        foreach (['condition', 'storage'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where('product_units.'.$field, $filters[$field]);
            }
        }

        if (isset($filters['min_price'])) {
            $query->where('product_units.selling_price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('product_units.selling_price', '<=', $filters['max_price']);
        }

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (Builder $query) use ($search): void {
                $query->where('product_units.sku', 'like', '%'.$search.'%')
                    ->orWhereHas('product', fn (Builder $productQuery) => $productQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('model_number', 'like', '%'.$search.'%')
                        ->orWhereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('name', 'like', '%'.$search.'%')));
            });
        }

        foreach (['brand', 'category'] as $relation) {
            if (! empty($filters[$relation])) {
                $query->whereHas('product.'.$relation, fn (Builder $relatedQuery) => $relatedQuery->where('slug', $filters[$relation]));
            }
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('product_units.selling_price'),
            'price_desc' => $query->orderByDesc('product_units.selling_price'),
            'name' => $query->orderBy(Product::query()->select('name')->whereColumn('products.id', 'product_units.product_id')),
            default => $query->latest('product_units.created_at'),
        };

        $units = $query->orderBy('product_units.id')->paginate(12)->withQueryString();
        $brands = Brand::query()->where('is_active', true)->orderBy('name')->get();
        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $storages = ProductUnit::query()->where('status', 'available')->whereNotNull('storage')->where('storage', '!=', '')
            ->whereHas('product', fn (Builder $query) => $query->whereIn('id', $this->products()->select('products.id')))
            ->distinct()->orderBy('storage')->pluck('storage');

        return $this->render('storefront.shop', compact('units', 'brands', 'categories', 'storages', 'filters'));
    }

    public function show(string $slug): View
    {
        $product = $this->products()->where('slug', $slug)
            ->with(['brand', 'category', 'units' => fn ($query) => $query->where('status', 'available')->orderBy('selling_price')])
            ->firstOrFail();

        return $this->render('storefront.product', compact('product'));
    }

    public function checkout(): View|RedirectResponse
    {
        $deliverySchedules = DeliverySchedule::query()
            ->where('is_published', true)
            ->where(fn (Builder $query) => $query->where('year', '>', now()->year)
                ->orWhere(fn (Builder $query) => $query->where('year', now()->year)->where('month', '>=', now()->month)))
            ->orderBy('year')->orderBy('month')->orderBy('city')->get();

        $view = $this->render('storefront.checkout', compact('deliverySchedules'));

        return $view->getData()['cartUnits']->isEmpty()
            ? redirect()->route('storefront.shop')->withErrors(['cart' => 'Add an available device before checkout.'])
            : $view;
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:254'],
            'phone' => ['required', 'string', 'max:40'],
            'state' => ['required', 'string', 'max:120'],
            'delivery_address' => ['required', 'string', 'max:2000'],
            'delivery_schedule_id' => [
                'required',
                'integer',
                Rule::exists('delivery_schedules', 'id')->where('is_published', true),
            ],
        ]);

        $unitIds = collect($request->session()->get('storefront.cart', []))
            ->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->unique()->values();

        if ($unitIds->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your bag is empty. Add an available device before checkout.']);
        }

        $schedule = DeliverySchedule::query()
            ->whereKey($validated['delivery_schedule_id'])
            ->where('is_published', true)
            ->where(fn (Builder $query) => $query->where('year', '>', now()->year)
                ->orWhere(fn (Builder $query) => $query->where('year', now()->year)->where('month', '>=', now()->month)))
            ->first();

        if (! $schedule) {
            throw ValidationException::withMessages(['delivery_schedule_id' => 'That delivery schedule is no longer available. Please choose another location.']);
        }

        $order = DB::transaction(function () use ($validated, $unitIds, $schedule) {
            $units = ProductUnit::query()
                ->whereIn('id', $unitIds)
                ->where('status', 'available')
                ->whereHas('product', fn (Builder $query) => $query->whereIn('products.id', $this->products()->select('products.id')))
                ->with('product')
                ->lockForUpdate()
                ->get();

            if ($units->count() !== $unitIds->count()) {
                throw ValidationException::withMessages(['cart' => 'A device in your bag is no longer available. Please review your bag.']);
            }

            $orderNumber = 'SC-'.Str::upper(Str::random(10));
            $order = Order::create([
                'order_number' => $orderNumber,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'state' => $validated['state'],
                'delivery_address' => $validated['delivery_address'],
                'delivery_city' => $schedule->city,
                'delivery_month' => $schedule->month,
                'delivery_year' => $schedule->year,
                'subtotal' => $units->sum(fn (ProductUnit $unit) => (float) $unit->selling_price),
                'status' => 'awaiting_payment',
                'payment_status' => 'pending',
            ]);

            foreach ($units as $unit) {
                $order->items()->create([
                    'product_unit_id' => $unit->id,
                    'product_name' => $unit->product->name,
                    'sku' => $unit->sku,
                    'storage' => $unit->storage,
                    'color' => $unit->color,
                    'condition' => $unit->condition,
                    'unit_price' => $unit->selling_price,
                ]);
            }

            ProductUnit::query()->whereIn('id', $unitIds)->update(['status' => 'reserved']);

            return $order;
        });

        $request->session()->forget('storefront.cart');
        $request->session()->flash('checkout_confirmation', [
            'order_number' => $order->order_number,
            'delivery_city' => $order->delivery_city,
            'delivery_state' => $order->state,
            'delivery_month' => DeliverySchedule::MONTHS[$order->delivery_month],
            'delivery_year' => $order->delivery_year,
            'payment' => config('storefront.payment'),
        ]);

        try {
            Mail::to($order->email)->send(new OrderPlaced($order->load('items')));
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect()->route('storefront.checkout.confirmation');
    }

    public function checkoutConfirmation(): View|RedirectResponse
    {
        $confirmation = session('checkout_confirmation');

        if (! $confirmation) {
            return redirect()->route('storefront.shop');
        }

        return $this->render('storefront.checkout-confirmation', compact('confirmation'));
    }

    public function trackOrder(): View
    {
        return $this->render('storefront.track-order', ['order' => null]);
    }

    public function findOrder(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:24'],
            'email' => ['required', 'email', 'max:254'],
        ]);
        $order = Order::query()->with('items')
            ->where('order_number', strtoupper($validated['order_number']))
            ->where('email', $validated['email'])
            ->first();

        if (! $order) {
            return back()->withInput()->withErrors(['order' => 'We could not find an order matching those details.']);
        }

        return $this->render('storefront.track-order', compact('order'));
    }

    private function render(string $view, array $data): View
    {
        $cartUnits = ProductUnit::query()->whereIn('id', session('storefront.cart', []))->where('status', 'available')
            ->whereHas('product', fn (Builder $query) => $query->whereIn('id', $this->products()->select('products.id')))
            ->with('product')->get();

        return view($view, [...$data, 'cartUnits' => $cartUnits]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $validated = $request->validate(['unit_id' => ['required', 'integer']]);
        $unit = ProductUnit::query()->where('status', 'available')
            ->whereHas('product', fn (Builder $query) => $query->whereIn('id', $this->products()->select('products.id')))
            ->findOrFail($validated['unit_id']);

        $cart = $request->session()->get('storefront.cart', []);
        $alreadyAdded = in_array($unit->id, $cart, true);
        $cart[] = $unit->id;
        $request->session()->put('storefront.cart', array_values(array_unique($cart)));

        return back()->with('success', $alreadyAdded ? 'This device is already in your cart.' : 'Device added to your cart.');
    }

    public function removeFromCart(Request $request, int $unit): RedirectResponse
    {
        $cart = array_filter($request->session()->get('storefront.cart', []), fn ($unitId) => (int) $unitId !== $unit);
        $request->session()->put('storefront.cart', array_values($cart));

        return back()->with('success', 'Device removed from your cart.');
    }
}