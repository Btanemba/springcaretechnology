<?php

namespace App\Jobs;

use App\Mail\NewProductUnitPosted;
use App\Models\EmailSubscriber;
use App\Models\ProductUnit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifySubscribersOfProductUnit implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public function __construct(public int $productUnitId)
    {
    }

    public function uniqueId(): string
    {
        return 'product-unit-newsletter-'.$this->productUnitId;
    }

    public function handle(): void
    {
        $unit = ProductUnit::query()->with(['product.brand', 'product.category'])->find($this->productUnitId);

        if (! $unit || $unit->newsletter_notified_at || ! $unit->isPubliclyPostedWithPhotos()) {
            return;
        }

        $product = $unit->product;
        $productUrl = route('storefront.product', $product->slug);
        $imageUrl = $unit->photoUrls()[0];

        EmailSubscriber::query()
            ->whereNotNull('confirmed_at')
            ->whereNull('unsubscribed_at')
            ->orderBy('id')
            ->chunkById(100, function ($subscribers) use ($unit, $product, $productUrl, $imageUrl): void {
                foreach ($subscribers as $subscriber) {
                    $unsubscribeUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'newsletter.unsubscribe',
                        now()->addYears(5),
                        ['subscriber' => $subscriber->getKey()],
                    );

                    Mail::to($subscriber->email)->queue(new NewProductUnitPosted(
                        productName: $product->name,
                        brandName: $product->brand->name,
                        categoryName: $product->category->name,
                        sku: $unit->sku,
                        condition: ProductUnit::CONDITIONS[$unit->condition] ?? $unit->condition,
                        price: \App\Support\Naira::format($unit->selling_price),
                        imageUrl: $imageUrl,
                        productUrl: $productUrl,
                        unsubscribeUrl: $unsubscribeUrl,
                    ));
                }
            });

        $unit->forceFill(['newsletter_notified_at' => now()])->saveQuietly();
    }
}