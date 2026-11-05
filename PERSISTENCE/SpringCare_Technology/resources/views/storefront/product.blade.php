@extends('storefront.layout')
@section('title', $product->name.' | SpringCare Technology')
@section('description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? 'Explore this device at SpringCare Technology.'), 155))

@section('content')
    @php($selectedUnit = $product->units->firstWhere('id', request()->integer('unit')) ?? $product->units->first())
    <div class="shell product-section">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('storefront.home') }}">Home</a><i data-lucide="chevron-right"></i><a href="{{ route('storefront.shop') }}">Shop</a><i data-lucide="chevron-right"></i><a href="{{ route('storefront.shop', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a><i data-lucide="chevron-right"></i><span aria-current="page">{{ $product->name }}</span></nav>
        @if($product->units->isNotEmpty())
            <section class="device-picker-row" aria-labelledby="unit-choices-title">
                <a class="text-link" href="{{ route('storefront.shop') }}"><i data-lucide="arrow-left"></i> All devices</a>
                <div class="unit-choice-section">
                    <div class="unit-choice-heading"><h2 id="unit-choices-title">Available individual units</h2><span>{{ $product->units->count() }} available</span></div>
                    <div class="unit-choice-list">
                        @foreach($product->units as $option)
                            @php($optionPhoto = $option->photoUrls()[0] ?? null)
                            <a class="unit-choice {{ $selectedUnit?->id === $option->id ? 'is-selected' : '' }}" href="{{ route('storefront.product', ['slug' => $product->slug, 'unit' => $option->id]) }}" @if($selectedUnit?->id === $option->id) aria-current="true" @endif>
                                <span class="unit-choice-photo">
                                    @if($optionPhoto)<img src="{{ $optionPhoto }}" alt="" loading="lazy" width="80" height="80">@else<i data-lucide="image"></i>@endif
                                </span>
                                <span class="unit-choice-info">
                                    <span class="unit-choice-sku">{{ $option->sku ?: 'SKU unavailable' }}</span>
                                    <strong>{{ implode(' / ', array_filter([$option->storage, $option->color])) ?: 'Device unit' }}</strong>
                                    <span class="unit-choice-condition">{{ \App\Models\ProductUnit::CONDITIONS[$option->condition] ?? $option->condition }}</span>
                                    <span class="unit-choice-price">{{ \App\Support\Naira::format($option->selling_price) }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
            @foreach($product->units as $unit)
                @php($photos = $unit->photoUrls())
                <section class="unit-panel" data-unit-panel="{{ $unit->id }}" @if($selectedUnit?->id !== $unit->id) hidden @endif aria-label="{{ $product->name }}, {{ $unit->sku ?: 'SKU unavailable' }}">
                    <div class="product-detail-grid">
                        <div class="device-gallery" data-gallery>
                            <div class="gallery-main device-photo">
                                @if(count($photos))<img src="{{ $photos[0] }}" alt="{{ $product->name }} - {{ $unit->color }} - photo 1" width="900" height="900" @if($selectedUnit?->id === $unit->id) fetchpriority="high" @else loading="lazy" @endif data-main-photo data-device-photo>@endif
                                <div class="photo-placeholder" @if(count($photos)) hidden @endif><i data-lucide="image"></i><span>Photos coming soon</span></div>
                                @if(count($photos) > 1)
                                    <div class="gallery-controls">
                                        <button class="gallery-control gallery-control-previous" type="button" data-photo-previous aria-label="Previous photo" title="Previous photo"><i data-lucide="chevron-left"></i></button>
                                        <span class="gallery-photo-count" data-photo-count aria-live="polite">1 / {{ count($photos) }}</span>
                                        <button class="gallery-control gallery-control-next" type="button" data-photo-next aria-label="Next photo" title="Next photo"><i data-lucide="chevron-right"></i></button>
                                    </div>
                                @endif
                                <span class="gallery-caption"><i data-lucide="camera"></i> This device &middot; {{ $unit->sku ?: 'SKU unavailable' }}</span>
                            </div>
                            @if(count($photos) > 1)<div class="gallery-thumbnails" aria-label="Device photos">@foreach($photos as $photo)<button class="gallery-thumbnail" type="button" data-photo-src="{{ $photo }}" data-photo-alt="{{ $product->name }} - {{ $unit->color }} - photo {{ $loop->iteration }}" aria-label="View photo {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"><img src="{{ $photo }}" alt="" loading="lazy" width="100" height="100"></button>@endforeach</div>@endif
                        </div>
                        <div class="device-info"><p class="eyebrow">{{ $product->brand->name }} / {{ $product->category->name }}</p><h1>{{ $product->name }}</h1>@if($product->model_number)<p class="model-number">Model {{ $product->model_number }}</p>@endif<div class="device-price-row"><strong class="device-price">{{ \App\Support\Naira::format($unit->selling_price) }}</strong><span class="availability"><span></span> Available</span></div>
                            <dl class="device-facts"><div><dt><i data-lucide="hard-drive"></i> Storage</dt><dd>{{ $unit->storage ?: 'Not specified' }}</dd></div><div><dt><i data-lucide="palette"></i> Color</dt><dd>{{ $unit->color ?: 'Not specified' }}</dd></div><div><dt><i data-lucide="scan-eye"></i> Condition</dt><dd>{{ \App\Models\ProductUnit::CONDITIONS[$unit->condition] ?? $unit->condition }}</dd></div><div><dt><i data-lucide="battery-charging"></i> Battery health</dt><dd>{{ $unit->battery_health !== null ? $unit->battery_health.'%' : 'Not specified' }}</dd></div></dl>
                            <div class="device-disclosure"><h2><i data-lucide="clipboard-list"></i> Condition notes</h2><p>{{ $unit->defects ?: 'No condition notes have been provided for this device.' }}</p></div><div class="device-disclosure"><h2><i data-lucide="package-check"></i> Included accessories</h2><p>{{ $unit->included_accessories ?: 'Accessories have not been specified for this device.' }}</p></div>
                            <form method="POST" action="{{ route('storefront.cart.add') }}" class="add-to-cart-form">@csrf<input type="hidden" name="unit_id" value="{{ $unit->id }}"><button class="button button-primary add-to-cart" type="submit"><i data-lucide="shopping-bag"></i> {{ in_array($unit->id, session('storefront.cart', []), true) ? 'Already in your bag' : 'Add to bag' }} <i data-lucide="arrow-right"></i></button></form><p class="device-cart-note">One individual device. Adding to your bag does not reserve stock.</p><p class="device-reference">Device reference: {{ $unit->sku ?: 'Unavailable' }}</p>
                        </div>
                    </div>
                </section>
            @endforeach
        @else
            <div class="empty-state sold-out"><i data-lucide="package-open"></i><p class="eyebrow">{{ $product->brand->name }}</p><h1>{{ $product->name }}</h1><h2>Currently out of stock.</h2><p>There are no available units of this model right now.</p><a class="button button-primary" href="{{ route('storefront.shop') }}">Explore other devices <i data-lucide="arrow-right"></i></a></div>
        @endif
        <section class="product-specifications" aria-labelledby="details-title"><div><p class="eyebrow">A CLOSER LOOK</p><h2 id="details-title">More about this device.</h2></div><div><h3>Overview</h3><div class="prose-text">{{ $product->description ?: 'A product description has not been added yet.' }}</div><h3>Specifications</h3><div class="prose-text specifications-text">{{ $product->specifications ?: 'Specifications have not been added yet.' }}</div></div></section>
    </div>
@endsection