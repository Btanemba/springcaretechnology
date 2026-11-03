@php($cardUnit = $product->units->first())
<article class="product-card">
    <a class="product-image-link" href="{{ route('storefront.product', ['slug' => $product->slug, 'unit' => $cardUnit?->id]) }}" tabindex="-1" aria-hidden="true">
        @include('storefront.partials.photo', ['unit' => $cardUnit, 'alt' => $product->name, 'photoClass' => 'product-card-photo'])
        @if($cardUnit)<span class="condition-badge">{{ \App\Models\ProductUnit::CONDITIONS[$cardUnit->condition] ?? $cardUnit->condition }}</span>@endif
        <span class="product-open"><i data-lucide="arrow-up-right"></i></span>
    </a>
    <div class="product-card-info"><p class="product-brand">{{ $product->brand->name }} <span>{{ $product->category->name }}</span></p><h3><a href="{{ route('storefront.product', ['slug' => $product->slug, 'unit' => $cardUnit?->id]) }}">{{ $product->name }}</a></h3><p class="product-variant">{{ implode(' / ', array_filter([$cardUnit?->storage, $cardUnit?->color])) ?: 'View device details' }}</p><div class="product-card-bottom"><strong><small>From</small> {{ \App\Support\Naira::format($cardUnit?->selling_price) }}</strong><span class="availability"><span></span> In stock</span></div></div>
</article>