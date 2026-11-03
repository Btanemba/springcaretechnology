@extends('storefront.layout')

@section('content')
    <section class="home-hero" aria-labelledby="hero-title">
        <img class="hero-image" src="https://images.unsplash.com/photo-1603891128711-11b4b03bb138?auto=format&fit=crop&w=2200&q=85" alt="A collection of smartphones" fetchpriority="high" width="2200" height="1400">
        <div class="hero-shade"></div>
        <div class="shell hero-content"><p class="eyebrow"><span class="live-dot"></span> A FRESH TAKE ON TECH</p><h1 id="hero-title">SpringCare<br>Technology<span>.</span></h1><p class="hero-description">Good tech. New possibilities.<br>Find the right device for whatever comes next.</p><a class="button button-primary" href="{{ route('storefront.shop') }}">Find your next device <i data-lucide="arrow-up-right"></i></a><div class="hero-note"><span class="hero-note-line"></span> Phones, possibilities, and a fresh start.</div></div>
        <div class="hero-index">01 / THE EVERYDAY COLLECTION</div>
    </section>

    <div class="value-strip"><div class="shell value-strip-inner"><span><i data-lucide="scan-eye"></i> Actual device details</span><span><i data-lucide="battery-charging"></i> Battery health, upfront</span><span><i data-lucide="sliders-horizontal"></i> A device for your needs</span></div></div>

    <section class="shell section" id="categories" aria-labelledby="categories-title">
        <div class="section-heading"><div><p class="eyebrow">FIND YOUR FIT</p><h2 id="categories-title">Your day. Your device.</h2></div><a class="text-link" href="{{ route('storefront.shop') }}">Browse everything <i data-lucide="arrow-right"></i></a></div>
        <div class="category-list">
            @forelse($categories as $category)
                <a class="category-link" href="{{ route('storefront.shop', ['category' => $category->slug]) }}"><span class="category-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span><strong>{{ $category->name }}</strong><small>{{ $category->description ?: 'Explore the collection' }}</small></span><i data-lucide="arrow-up-right"></i></a>
            @empty
                <div class="inline-empty">New categories are on the way. <a class="text-link" href="{{ route('storefront.shop') }}">Explore the shop <i data-lucide="arrow-right"></i></a></div>
            @endforelse
        </div>
    </section>

    <section class="collection-band" aria-labelledby="featured-title"><div class="shell section {{ $showAcademyAdvertisement ? 'featured-layout' : '' }}">
        <div @class(['featured-content' => $showAcademyAdvertisement])>
            <div class="section-heading"><div><p class="eyebrow">THE CURRENT EDIT</p><h2 id="featured-title">Meet your next upgrade.</h2></div><a class="text-link" href="{{ route('storefront.shop') }}">Shop all devices <i data-lucide="arrow-right"></i></a></div>
            <div class="product-grid featured-grid">@forelse($products as $product) @include('storefront.partials.product-card') @empty <div class="empty-state"><i data-lucide="package-open"></i><h3>A new collection is on its way.</h3><p>Available devices will appear here as they are published.</p><a href="{{ route('storefront.shop') }}" class="text-link">Visit the shop <i data-lucide="arrow-right"></i></a></div> @endforelse</div>
        </div>
        @if($showAcademyAdvertisement)
        <aside class="academy-ad" aria-labelledby="academy-ad-title">
            <p class="eyebrow academy-ad-label">DAYCARE &amp; EARLY LEARNING <span>ADVERTISEMENT</span></p>
            <a class="academy-ad-photo" href="https://springcareacademy.ng/" target="_blank" rel="noopener noreferrer" aria-label="Explore SpringCare Academy (opens in a new tab)">
                <img src="https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=800&q=85" alt="A child enjoying creative play" width="800" height="600" loading="lazy">
            </a>
            <div class="academy-ad-copy">
                <p class="academy-ad-kicker">SPRINGCARE ACADEMY · DAYCARE &amp; EARLY LEARNING</p>
                <h2 id="academy-ad-title">Looking for a daycare or nursery for your child?</h2>
                <p>A caring place for little ones to learn, play, and grow. Opening September 2027.</p>
                <div class="academy-ad-opening"><i data-lucide="calendar-days"></i><span>Opening September 2027</span></div>
                <a class="button academy-ad-button" href="https://springcareacademy.ng/" target="_blank" rel="noopener noreferrer" aria-label="Explore SpringCare Academy (opens in a new tab)">Explore our daycare <i data-lucide="arrow-up-right"></i></a>
            </div>
        </aside>
        @endif
    </div></section>

    <section class="shell section approach-section" id="our-approach" aria-labelledby="approach-title">
        <div class="approach-heading"><p class="eyebrow">THE SPRINGCARE APPROACH</p><h2 id="approach-title">The details matter.<br>So does your next device.</h2><p>Know what you are choosing. Explore individual devices with the information that matters, all in one place.</p><a class="text-link" href="{{ route('storefront.shop') }}">Find your fit <i data-lucide="arrow-right"></i></a></div>
        <div class="approach-points"><article><span>01</span><div><h3>The actual device</h3><p>Browse uploaded photos of each available unit, not just a model name.</p></div><i data-lucide="camera"></i></article><article><span>02</span><div><h3>Condition without the guesswork</h3><p>See listed condition, battery health, and any disclosed defects before choosing.</p></div><i data-lucide="scan-eye"></i></article><article><span>03</span><div><h3>Know what comes with it</h3><p>Check storage, color, and included accessories for the device you select.</p></div><i data-lucide="package-check"></i></article></div>
    </section>
@endsection