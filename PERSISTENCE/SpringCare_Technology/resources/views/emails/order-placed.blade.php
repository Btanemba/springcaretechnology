<h1>We received your order</h1>
<p>Thank you, {{ $order->first_name }} {{ $order->last_name }}. Your order is awaiting payment verification.</p>
<p><strong>Order reference:</strong> {{ $order->order_number }}</p>
<p><strong>Delivery to:</strong> {{ $order->delivery_city }}, {{ $order->state }} · {{ \App\Models\DeliverySchedule::MONTHS[$order->delivery_month] }} {{ $order->delivery_year }}</p>

<h2>Items</h2>
<ul>
    @foreach($order->items as $item)
        <li>{{ $item->product_name }}@if($item->storage || $item->color) ({{ implode(' / ', array_filter([$item->storage, $item->color])) }})@endif — {{ \App\Support\Naira::format($item->unit_price) }}</li>
    @endforeach
</ul>
<p><strong>Total:</strong> {{ \App\Support\Naira::format($order->subtotal) }}</p>

@if($payment['bank_name'] && $payment['account_name'] && $payment['account_number'])
    <h2>Bank transfer details</h2>
    <p>Bank: {{ $payment['bank_name'] }}<br>Account name: {{ $payment['account_name'] }}<br>Account number: {{ $payment['account_number'] }}<br>Transfer reference: {{ $order->order_number }}</p>
@else
    <p>Payment instructions are not available yet. Please contact SpringCare and include your order reference.</p>
@endif

<p>Track your order at <a href="{{ route('storefront.track-order', ['order_number' => $order->order_number]) }}">SpringCare order tracking</a>. Enter this order reference and your checkout email address.</p>