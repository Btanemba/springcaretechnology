@extends('storefront.layout')

@section('title', 'Checkout | SpringCare Technology')

@section('content')
    <section class="shell checkout-page">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('storefront.shop') }}">Shop</a><i data-lucide="chevron-right"></i><span aria-current="page">Checkout</span></nav>
        <div class="checkout-heading"><p class="eyebrow">ALMOST YOURS</p><h1>Delivery details</h1><p>Your order is only confirmed after payment is verified.</p></div>

        <div class="checkout-grid">
            <form class="checkout-form" method="POST" action="{{ route('storefront.checkout.place') }}">
                @csrf
                <section class="checkout-fields" aria-labelledby="contact-heading">
                    <h2 id="contact-heading">Contact</h2>
                    <label for="first_name">First name <span class="required-indicator" aria-hidden="true">*</span></label>
                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" maxlength="80" required>
                    <label for="last_name">Last name <span class="required-indicator" aria-hidden="true">*</span></label>
                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" maxlength="80" required>
                    <label for="email">Email address <span class="required-indicator" aria-hidden="true">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required>
                    <label for="phone">Phone number <span class="required-indicator" aria-hidden="true">*</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="40" required>
                </section>

                <section class="checkout-fields" aria-labelledby="delivery-heading">
                    <h2 id="delivery-heading">Delivery</h2>
                    <label for="delivery_schedule_id">Delivery location and estimate <span class="required-indicator" aria-hidden="true">*</span></label>
                    <select id="delivery_schedule_id" name="delivery_schedule_id" required>
                        <option value="">Choose a location</option>
                        @foreach($deliverySchedules as $schedule)
                            <option value="{{ $schedule->id }}" @selected(old('delivery_schedule_id') == $schedule->id)>
                                {{ $schedule->city }} · {{ \App\Models\DeliverySchedule::MONTHS[$schedule->month] }} {{ $schedule->year }}
                            </option>
                        @endforeach
                    </select>
                    @if($deliverySchedules->isEmpty())
                        <p class="checkout-help">There are no published delivery locations available right now.</p>
                    @endif
                    <label for="state">State / FCT <span class="required-indicator" aria-hidden="true">*</span></label>
                    <input id="state" name="state" value="{{ old('state') }}" autocomplete="address-level1" maxlength="120" required>
                    <label for="delivery_address">Street address and delivery notes <span class="required-indicator" aria-hidden="true">*</span></label>
                    <textarea id="delivery_address" name="delivery_address" rows="4" maxlength="2000" autocomplete="street-address" required>{{ old('delivery_address') }}</textarea>
                </section>

                <button class="button button-primary checkout-submit" type="submit" @disabled($deliverySchedules->isEmpty())>Place order <i data-lucide="arrow-right"></i></button>
            </form>

            <aside class="checkout-summary" aria-labelledby="summary-heading">
                <h2 id="summary-heading">Your order</h2>
                @foreach($cartUnits as $unit)
                    <div class="checkout-item"><span>{{ $unit->product->name }}@if($unit->storage || $unit->color)<small>{{ implode(' / ', array_filter([$unit->storage, $unit->color])) }}</small>@endif</span><strong>{{ \App\Support\Naira::format($unit->selling_price) }}</strong></div>
                @endforeach
                <div class="checkout-total"><span>Subtotal</span><strong>{{ \App\Support\Naira::format($cartUnits->sum('selling_price')) }}</strong></div>
                <p>Payment instructions will be shown after you place your order. Devices are held while payment is pending.</p>
            </aside>
        </div>
    </section>
@endsection