<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirm email updates | SpringCare Technology</title>
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
</head>
<body>
    <main class="shell section">
        <div class="empty-state">
            <p class="eyebrow">SPRINGCARE TECHNOLOGY</p>
            <h1>Confirm new-device updates</h1>
            <p>Confirm to receive an email when an individual device with photos is posted.</p>
            @if($subscriber->confirmed_at && ! $subscriber->unsubscribed_at)
                <p>This email address is already subscribed.</p>
            @else
                <form action="{{ url()->full() }}" method="POST">
                    @csrf
                    <button class="button button-primary" type="submit">Confirm subscription</button>
                </form>
            @endif
            <p><a class="text-link" href="{{ route('storefront.home') }}">Return to the shop</a></p>
        </div>
    </main>
</body>
</html>