<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Explore SpringCare Technology. Discover available devices, compare their condition, and find the technology that fits your day.')">
    <meta name="theme-color" content="#176b53">
    <title>@yield('title', 'SpringCare Technology | Devices for your everyday')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;450;500;550;600;650;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
    <script defer src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="{{ asset('js/price-format.js') }}"></script>
    <script defer src="{{ asset('js/storefront.js') }}"></script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="announcement">A fresh start for your everyday tech. <a href="{{ route('storefront.shop') }}">Explore the collection <span aria-hidden="true">&rarr;</span></a></div>
    <header class="site-header">
        <div class="shell header-inner">
            <a class="wordmark" href="{{ route('storefront.home') }}" aria-label="SpringCare Technology home">
                <span class="brand-symbol" aria-hidden="true"><i data-lucide="sprout"></i></span>
                <span>SpringCare<span class="wordmark-sub">TECHNOLOGY</span></span>
            </a>
            <nav class="desktop-nav" aria-label="Main navigation">
                <a href="{{ route('storefront.home') }}" @if(request()->routeIs('storefront.home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('storefront.shop') }}" @if(request()->routeIs('storefront.shop', 'storefront.product')) aria-current="page" @endif>Shop all</a>
                <a href="{{ route('storefront.home') }}#categories">Categories</a>
                <a href="{{ route('storefront.home') }}#our-approach">Our approach</a>
            </nav>
            <div class="header-actions">
                <a class="icon-button" href="{{ route('storefront.shop') }}#search" aria-label="Search devices" title="Search devices"><i data-lucide="search"></i></a>
                <button class="icon-button cart-trigger" type="button" data-open-cart aria-label="Open shopping cart, {{ $cartUnits->count() }} devices" title="Shopping cart"><i data-lucide="shopping-bag"></i><span class="cart-count">{{ $cartUnits->count() }}</span></button>
                <button class="icon-button mobile-menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="mobile-nav" data-menu-toggle title="Navigation"><i data-lucide="menu"></i></button>
            </div>
        </div>
        <nav id="mobile-nav" class="mobile-nav shell" aria-label="Mobile navigation" hidden>
            <a href="{{ route('storefront.home') }}">Home</a>
            <a href="{{ route('storefront.shop') }}">Shop all</a>
            <a href="{{ route('storefront.home') }}#categories">Categories</a>
            <a href="{{ route('storefront.home') }}#our-approach">Our approach</a>
        </nav>
    </header>

    @if(session('success'))
        <div class="shell"><div class="notice" role="status"><i data-lucide="circle-check"></i><span>{{ session('success') }}</span><button type="button" class="text-link" data-open-cart>View cart</button></div></div>
    @endif
    @if($errors->any())
        <div class="shell"><div class="notice notice-error" role="alert"><div><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div></div>
    @endif

    <main id="main">@yield('content')</main>

    <footer class="site-footer">
        <div class="shell footer-main">
            <div><a class="footer-brand" href="{{ route('storefront.home') }}">SpringCare Technology<span class="brand-dot">.</span></a><p>A fresh perspective on everyday technology.</p></div>
            <section class="footer-subscribe" aria-labelledby="newsletter-title">
                <p class="newsletter-eyebrow">DEVICE DROPS</p>
                <h2 id="newsletter-title">Be first to find your next device.</h2>
                <p class="newsletter-description">Get a note when a new photo-ready device is posted.</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                    @csrf
                    <input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" placeholder="Email address" aria-label="Email address" autocomplete="email" maxlength="254" required>
                    <button class="button button-primary" type="submit">Subscribe <i data-lucide="arrow-right"></i></button>
                </form>
                @if(session('newsletter_status'))
                    <p class="newsletter-status" role="status" data-newsletter-status data-type="{{ session('newsletter_status_type', 'success') }}" data-title="{{ session('newsletter_status_title', 'Newsletter update') }}">{{ session('newsletter_status') }}</p>
                @endif
            </section>
            <nav aria-label="Footer navigation"><a href="{{ route('storefront.shop') }}">Shop devices <i data-lucide="arrow-up-right"></i></a><a href="{{ route('storefront.home') }}#our-approach">Our approach <i data-lucide="arrow-up-right"></i></a></nav>
        </div>
        <div class="shell footer-bottom"><span>&copy; {{ date('Y') }} SpringCare Technology</span><span>All prices in Nigerian naira (₦).</span><a href="#main">Back to top &uarr;</a></div>
    </footer>

    <dialog class="cart-dialog" id="cart-dialog" aria-labelledby="cart-title">
        <div class="drawer-heading"><div><p class="eyebrow">YOUR SELECTION</p><h2 id="cart-title">Shopping bag <span>({{ $cartUnits->count() }})</span></h2></div><button type="button" class="icon-button" data-close-cart aria-label="Close cart" title="Close cart"><i data-lucide="x"></i></button></div>
        <div class="cart-body">
            @forelse($cartUnits as $cartUnit)
                <article class="cart-item">
                    @include('storefront.partials.photo', ['unit' => $cartUnit, 'alt' => $cartUnit->product->name, 'photoClass' => 'cart-photo'])
                    <div class="cart-item-details"><a href="{{ route('storefront.product', $cartUnit->product->slug) }}">{{ $cartUnit->product->name }}</a><p>{{ implode(' / ', array_filter([$cartUnit->storage, $cartUnit->color, \App\Models\ProductUnit::CONDITIONS[$cartUnit->condition] ?? $cartUnit->condition])) }}</p><strong>{{ \App\Support\Naira::format($cartUnit->selling_price) }}</strong></div>
                    <form method="POST" action="{{ route('storefront.cart.remove', $cartUnit->id) }}">@csrf @method('DELETE')<button class="icon-button" aria-label="Remove {{ $cartUnit->product->name }}" title="Remove device"><i data-lucide="trash-2"></i></button></form>
                </article>
            @empty
                <div class="empty-state"><i data-lucide="shopping-bag"></i><h3>A little room for possibility.</h3><p>Your bag is empty. Find a device that fits your day.</p><a class="button button-primary" href="{{ route('storefront.shop') }}">Explore devices <i data-lucide="arrow-right"></i></a></div>
            @endforelse
        </div>
        @if($cartUnits->isNotEmpty())
            <div class="cart-summary"><div><span>Subtotal</span><strong>{{ \App\Support\Naira::format($cartUnits->sum('selling_price')) }}</strong></div><p>Adding a device does not reserve it. Availability is subject to change. Online checkout is not yet available.</p><a class="button button-primary" href="{{ route('storefront.shop') }}">Continue shopping <i data-lucide="arrow-right"></i></a></div>
        @endif
    </dialog>
</body>
</html>