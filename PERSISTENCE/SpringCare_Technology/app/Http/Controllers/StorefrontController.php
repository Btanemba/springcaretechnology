<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
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

        $showAcademyAdvertisement = $advertisement->isVisible();

        return $this->render('storefront.home', compact('products', 'categories', 'showAcademyAdvertisement'));
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

        $matchingUnits = function ($query) use ($filters): void {
            $query->where('status', 'available');

            foreach (['condition', 'storage'] as $field) {
                if (isset($filters[$field]) && $filters[$field] !== '') {
                    $query->where($field, $filters[$field]);
                }
            }

            if (isset($filters['min_price'])) {
                $query->where('selling_price', '>=', $filters['min_price']);
            }

            if (isset($filters['max_price'])) {
                $query->where('selling_price', '<=', $filters['max_price']);
            }
        };

        $query = $this->products()->whereHas('units', $matchingUnits)
            ->with(['brand', 'category', 'units' => function ($query) use ($matchingUnits): void {
                $matchingUnits($query);
                $query->orderBy('selling_price');
            }])
            ->withMin(['units as lowest_price' => $matchingUnits], 'selling_price');

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('model_number', 'like', '%'.$search.'%')
                    ->orWhereHas('brand', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'));
            });
        }

        foreach (['brand', 'category'] as $relation) {
            if (! empty($filters[$relation])) {
                $query->whereHas($relation, fn (Builder $query) => $query->where('slug', $filters[$relation]));
            }
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('lowest_price'),
            'price_desc' => $query->orderByDesc('lowest_price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $products = $query->orderBy('products.id')->paginate(12)->withQueryString();
        $brands = Brand::query()->where('is_active', true)->orderBy('name')->get();
        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $storages = ProductUnit::query()->where('status', 'available')->whereNotNull('storage')->where('storage', '!=', '')
            ->whereHas('product', fn (Builder $query) => $query->whereIn('id', $this->products()->select('products.id')))
            ->distinct()->orderBy('storage')->pluck('storage');

        return $this->render('storefront.shop', compact('products', 'brands', 'categories', 'storages', 'filters'));
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