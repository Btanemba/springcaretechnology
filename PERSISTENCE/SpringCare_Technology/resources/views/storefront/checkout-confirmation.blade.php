@extends('storefront.layout')

@section('title', 'Order received | SpringCare Technology')

@section('content')
    <section class="shell checkout-page checkout-confirmation">
        <div data-order-confirmation
            data-order-number="{{ $confirmation['order_number'] }}"
            data-delivery-city="{{ $confirmation['delivery_city'] }}"
            data-delivery-state="{{ $confirmation['delivery_state'] }}"
            data-delivery-month="{{ $confirmation['delivery_month'] }}"
            data-delivery-year="{{ $confirmation['delivery_year'] }}"></div>
        <p class="eyebrow">ORDER RECEIVED</p>
        <h1>Thanks, your order is in.</h1>
        <p class="checkout-order-number">Order reference <strong>{{ $confirmation['order_number'] }}</strong></p>
        <p>Estimated delivery to <strong>{{ $confirmation['delivery_city'] }}, {{ $confirmation['delivery_state'] }}</strong> is <strong>{{ $confirmation['delivery_month'] }} {{ $confirmation['delivery_year'] }}</strong>.</p>
        <section class="payment-instructions" aria-labelledby="payment-heading">
            <h2 id="payment-heading">Payment by bank transfer</h2>
            @if($confirmation['payment']['bank_name'] && $confirmation['payment']['account_name'] && $confirmation['payment']['account_number'])
                <dl>
                    <div><dt>Bank</dt><dd>{{ $confirmation['payment']['bank_name'] }}</dd></div>
                    <div><dt>Account name</dt><dd>{{ $confirmation['payment']['account_name'] }}</dd></div>
                    <div><dt>Account number</dt><dd>{{ $confirmation['payment']['account_number'] }}</dd></div>
                    <div><dt>Transfer reference</dt><dd>{{ $confirmation['order_number'] }}</dd></div>
                </dl>
                <p>Your order is awaiting payment verification. Use your order reference as the transfer narration.</p>
            @else
                <p>Payment instructions are not available yet. Please contact SpringCare with your order reference.</p>
            @endif
        </section>
        <a class="button button-primary" href="{{ route('storefront.shop') }}">Continue shopping <i data-lucide="arrow-right"></i></a>
    </section>
@endsection