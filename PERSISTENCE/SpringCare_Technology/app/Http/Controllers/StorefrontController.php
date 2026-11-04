<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliverySchedule;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Support\AcademyAdvertisement;
use App\Support\Naira;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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