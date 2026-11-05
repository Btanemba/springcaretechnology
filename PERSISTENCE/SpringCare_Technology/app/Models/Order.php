<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number',
    'first_name',
    'last_name',
    'email',
    'phone',
    'state',
    'delivery_address',
    'delivery_city',
    'delivery_month',
    'delivery_year',
    'subtotal',
    'status',
    'payment_status',
    'payment_reference',
    'payment_confirmed_at',
])]
class Order extends Model
{
    public const STATUSES = [
        'awaiting_payment' => 'Awaiting Payment',
        'payment_confirmed' => 'Payment Confirmed',
        'received_at_hq' => 'Order Received at HQ',
        'in_delivery' => 'In Delivery',
        'at_destination' => 'At Destination',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'rejected' => 'Rejected',
    ];

    protected function casts(): array
    {
        return [
            'delivery_month' => 'integer',
            'delivery_year' => 'integer',
            'subtotal' => 'decimal:2',
            'payment_confirmed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            $fulfillmentStatuses = ['payment_confirmed', 'received_at_hq', 'in_delivery', 'at_destination', 'delivered'];

            if ($order->payment_status === 'confirmed' || in_array($order->status, $fulfillmentStatuses, true)) {
                $order->payment_status = 'confirmed';
                $order->payment_confirmed_at ??= now();
                if ($order->status === 'awaiting_payment') {
                    $order->status = 'payment_confirmed';
                }
            }
        });

        static::updated(function (Order $order): void {
            if (! $order->wasChanged('status')) {
                return;
            }

            $unitIds = $order->items()->whereNotNull('product_unit_id')->pluck('product_unit_id');

            if ($order->status === 'delivered') {
                ProductUnit::query()->whereIn('id', $unitIds)->update(['status' => 'sold']);
            } elseif (in_array($order->status, ['cancelled'], true)) {
                ProductUnit::query()->whereIn('id', $unitIds)->where('status', 'reserved')->update(['status' => 'available']);
            }
        });
    }
}