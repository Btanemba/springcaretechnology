@extends('storefront.layout')

@section('title', 'Track an order | SpringCare Technology')

@section('content')
    <section class="shell checkout-page tracking-page">
        <p class="eyebrow">ORDER UPDATES</p>
        <h1>Track your order</h1>
        <p class="tracking-intro">Enter the order reference and checkout email address to view its latest status.</p>
        <form class="tracking-form" method="POST" action="{{ route('storefront.track-order.find') }}">
            @csrf
            <label for="order_number">Order reference</label>
            <input id="order_number" name="order_number" value="{{ old('order_number', request('order_number')) }}" maxlength="24" required>
            <label for="tracking_email">Checkout email</label>
            <input id="tracking_email" name="email" type="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" required>
            <button class="button button-primary" type="submit">Find order <i data-lucide="search"></i></button>
        </form>

        @if($order)
            <section class="tracking-result" aria-live="polite">
                <p class="eyebrow">{{ $order->order_number }}</p>
                <h2>{{ \App\Models\Order::STATUSES[$order->status] ?? $order->status }}</h2>
                <p>Payment: {{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }}</p>
                <p>Delivery to <strong>{{ $order->delivery_city }}</strong> is scheduled for <strong>{{ \App\Models\DeliverySchedule::MONTHS[$order->delivery_month] }} {{ $order->delivery_year }}</strong>.</p>
                <ul>
                    @foreach($order->items as $item)
                        <li>{{ $item->product_name }}@if($item->storage || $item->color) · {{ implode(' / ', array_filter([$item->storage, $item->color])) }}@endif</li>
                    @endforeach
                </ul>
            </section>
        @endif
    </section>
@endsection