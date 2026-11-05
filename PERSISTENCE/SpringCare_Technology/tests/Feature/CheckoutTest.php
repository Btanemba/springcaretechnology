<?php

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliverySchedule;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_reserves_unit_emails_payment_instructions_and_shows_delivery_popup_data(): void
    {
        Mail::fake();
        $unit = $this->availableUnit();
        $schedule = DeliverySchedule::create([
            'city' => 'Lagos',
            'month' => now()->month,
            'year' => now()->year,
            'is_published' => true,
        ]);

        $this->withSession(['storefront.cart' => [$unit->id]])
            ->post(route('storefront.checkout.place'), [
                'first_name' => 'Ada',
                'last_name' => 'Customer',
                'email' => 'ada@example.com',
                'phone' => '08012345678',
                'delivery_address' => '12 Example Street, Lagos',
                'delivery_schedule_id' => $schedule->id,
            ])
            ->assertRedirect(route('storefront.checkout.confirmation'));

        $order = Order::query()->with('items')->firstOrFail();
        $this->assertSame('awaiting_payment', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('reserved', $unit->fresh()->status);
        $this->assertCount(1, $order->items);
        Mail::assertSent(OrderPlaced::class);

        $this->get(route('storefront.checkout.confirmation'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Lagos')
            ->assertSee(\App\Models\DeliverySchedule::MONTHS[now()->month].' '.now()->year);
    }

    public function test_order_tracking_requires_both_order_number_and_checkout_email(): void
    {
        $order = Order::create([
            'order_number' => 'SC-TRACK1234',
            'first_name' => 'Ada',
            'last_name' => 'Customer',
            'email' => 'ada@example.com',
            'phone' => '08012345678',
            'delivery_address' => '12 Example Street, Lagos',
            'delivery_city' => 'Lagos',
            'delivery_month' => now()->month,
            'delivery_year' => now()->year,
            'subtotal' => 145000,
            'status' => 'awaiting_payment',
            'payment_status' => 'pending',
        ]);

        $this->post(route('storefront.track-order.find'), [
            'order_number' => $order->order_number,
            'email' => 'wrong@example.com',
        ])->assertSessionHasErrors('order');

        $this->post(route('storefront.track-order.find'), [
            'order_number' => $order->order_number,
            'email' => $order->email,
        ])->assertOk()->assertSee('Awaiting Payment')->assertSee('Lagos');
    }

    private function availableUnit(): \App\Models\ProductUnit
    {
        $brand = Brand::create(['name' => 'Samsung', 'slug' => 'samsung', 'is_active' => true]);
        $category = Category::create(['name' => 'Phones', 'slug' => 'phones', 'is_active' => true, 'sort_order' => 0]);
        $product = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Samsung S25',
            'slug' => 'samsung-s25',
            'status' => 'published',
        ]);

        return $product->units()->create([
            'sku' => 'S25-001',
            'condition' => 'good',
            'selling_price' => 145000,
            'status' => 'available',
        ]);
    }
}