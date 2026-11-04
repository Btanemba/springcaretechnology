<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribe | SpringCare Technology</title>
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
</head>
<body>
    <main class="shell section">
        <div class="empty-state">
            <p class="eyebrow">SPRINGCARE TECHNOLOGY</p>
            <h1>Unsubscribe from device updates</h1>
            @if($subscriber->unsubscribed_at)
                <p>This email address is already unsubscribed.</p>
            @else
                <p>Stop receiving emails when new device units are posted?</p>
                <form action="{{ url()->full() }}" method="POST">
                    @csrf
                    <button class="button button-secondary" type="submit">Unsubscribe</button>
                </form>
            @endif
            <p><a class="text-link" href="{{ route('storefront.home') }}">Return to the shop</a></p>
        </div>
    </main>
</body>
</html>