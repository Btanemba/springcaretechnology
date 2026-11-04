<?php

namespace Tests\Feature;

use App\Mail\ConfirmNewsletterSubscription;
use App\Jobs\NotifySubscribersOfProductUnit;
use App\Mail\NewProductUnitPosted;
use App\Models\Brand;
use App\Models\Category;
use App\Models\EmailSubscriber;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribing_sends_a_confirmation_email_without_activating_the_address(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'Reader@Example.com'])
            ->assertRedirect();

        $subscriber = EmailSubscriber::firstOrFail();

        $this->assertSame('reader@example.com', $subscriber->email);
        $this->assertNull($subscriber->confirmed_at);
        Mail::assertQueued(ConfirmNewsletterSubscription::class);
    }

    public function test_signed_confirmation_activates_the_subscription_only_after_post(): void
    {
        $subscriber = EmailSubscriber::create(['email' => 'reader@example.com']);
        $url = URL::temporarySignedRoute('newsletter.confirm', now()->addHour(), [
            'subscriber' => $subscriber->getKey(),
        ]);

        $this->get($url)->assertOk()->assertSee('Confirm subscription');
        $this->assertNull($subscriber->fresh()->confirmed_at);

        $this->post($url)->assertRedirect(route('storefront.home'));
        $this->assertNotNull($subscriber->fresh()->confirmed_at);
    }

    public function test_expired_confirmation_link_is_rejected(): void
    {
        $subscriber = EmailSubscriber::create(['email' => 'reader@example.com']);
        $url = URL::temporarySignedRoute('newsletter.confirm', now()->subMinute(), [
            'subscriber' => $subscriber->getKey(),
        ]);

        $this->get($url)->assertForbidden();
        $this->assertNull($subscriber->fresh()->confirmed_at);
    }

    public function test_available_unit_with_photos_dispatches_a_notification_after_it_is_saved(): void
    {
        Queue::fake();
        $product = $this->createProduct('published');

        $unit = $product->units()->create([
            'sku' => 'DEVICE-001',
            'condition' => 'good',
            'selling_price' => 50000,
            'status' => 'available',
            'photos' => ['product-units/device-001.jpg'],
        ]);

        Queue::assertPushed(NotifySubscribersOfProductUnit::class, fn ($job) => $job->productUnitId === $unit->id);
    }

    public function test_unit_without_photos_or_a_published_parent_does_not_dispatch_a_notification(): void
    {
        Queue::fake();
        $publishedProduct = $this->createProduct('published');
        $draftProduct = $this->createProduct('draft', 'other-device');

        $publishedProduct->units()->create([
            'sku' => 'DEVICE-002',
            'condition' => 'good',
            'selling_price' => 50000,
            'status' => 'available',
        ]);
        $draftProduct->units()->create([
            'sku' => 'DEVICE-003',
            'condition' => 'good',
            'selling_price' => 50000,
            'status' => 'available',
            'photos' => ['product-units/device-003.jpg'],
        ]);

        Queue::assertNotPushed(NotifySubscribersOfProductUnit::class);
    }

    public function test_adding_photos_to_an_existing_available_unit_dispatches_a_notification(): void
    {
        Queue::fake();
        $product = $this->createProduct('published');
        $unit = $product->units()->create([
            'sku' => 'DEVICE-006',
            'condition' => 'good',
            'selling_price' => 50000,
            'status' => 'available',
        ]);

        $unit->update(['photos' => ['product-units/device-006.jpg']]);

        Queue::assertPushed(NotifySubscribersOfProductUnit::class, fn ($job) => $job->productUnitId === $unit->id);
    }

    public function test_publishing_a_product_dispatches_notifications_for_existing_eligible_units(): void
    {
        Queue::fake();
        $product = $this->createProduct('draft');
        $unit = $product->units()->create([
            'sku' => 'DEVICE-004',
            'condition' => 'good',
            'selling_price' => 50000,
            'status' => 'available',
            'photos' => ['product-units/device-004.jpg'],
        ]);

        $product->update(['status' => 'published']);

        Queue::assertPushed(NotifySubscribersOfProductUnit::class, fn ($job) => $job->productUnitId === $unit->id);
    }

    public function test_unit_notification_only_goes_to_confirmed_subscribers_who_have_not_unsubscribed(): void
    {
        Mail::fake();
        $product = $this->createProduct('published');
        $unit = $product->units()->create([
            'sku' => 'DEVICE-005',
            'condition' => 'good',
            'selling_price' => 50000,
            'status' => 'available',
            'photos' => ['product-units/device-005.jpg'],
        ]);
        EmailSubscriber::create(['email' => 'confirmed@example.com', 'confirmed_at' => now()]);
        EmailSubscriber::create(['email' => 'pending@example.com']);
        EmailSubscriber::create([
            'email' => 'unsubscribed@example.com',
            'confirmed_at' => now(),
            'unsubscribed_at' => now(),
        ]);

        (new NotifySubscribersOfProductUnit($unit->id))->handle();

        Mail::assertQueued(NewProductUnitPosted::class, 1);
        Mail::assertQueued(NewProductUnitPosted::class, fn ($mail) => $mail->hasTo('confirmed@example.com'));
        $this->assertNotNull($unit->fresh()->newsletter_notified_at);
    }

    private function createProduct(string $status, string $suffix = 'device'): Product
    {
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand-'.$suffix, 'is_active' => true]);
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-'.$suffix,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Test Device',
            'slug' => 'test-device-'.$suffix,
            'status' => $status,
        ]);
    }
}