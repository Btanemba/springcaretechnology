@php($product = $unit->product)
<article class="product-card">
    <a class="product-image-link" href="{{ route('storefront.product', ['slug' => $product->slug, 'unit' => $unit->id]) }}" tabindex="-1" aria-hidden="true">
        @include('storefront.partials.photo', ['unit' => $unit, 'alt' => $product->name, 'photoClass' => 'product-card-photo'])
        <span class="condition-badge">{{ \App\Models\ProductUnit::CONDITIONS[$unit->condition] ?? $unit->condition }}</span>
        <span class="product-open"><i data-lucide="arrow-up-right"></i></span>
    </a>
    <div class="product-card-info">
        <p class="product-brand">{{ $product->brand->name }} <span>{{ $product->category->name }}</span></p>
        <h3><a href="{{ route('storefront.product', ['slug' => $product->slug, 'unit' => $unit->id]) }}">{{ $product->name }}</a></h3>
        <p class="product-variant">{{ $unit->sku }}@if($unit->storage || $unit->color) &middot; {{ implode(' / ', array_filter([$unit->storage, $unit->color])) }}@endif</p>
        <div class="product-card-bottom"><strong>{{ \App\Support\Naira::format($unit->selling_price) }}</strong><span class="availability"><span></span> Available</span></div>
    </div>
</article>