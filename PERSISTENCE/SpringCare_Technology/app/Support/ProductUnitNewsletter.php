<?php

namespace App\Support;

use App\Mail\NewProductUnitPosted;
use App\Models\EmailSubscriber;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class ProductUnitNewsletter
{
    public function send(ProductUnit $unit): void
    {
        if ($unit->newsletter_notified_at || ! $unit->isPubliclyPostedWithPhotos()) {
            return;
        }

        $product = $unit->product()->with(['brand', 'category'])->first();
        $productUrl = route('storefront.product', $product->slug);
        $imageUrl = $unit->photoUrls()[0];
        $sentSuccessfully = true;

        EmailSubscriber::query()
            ->whereNotNull('confirmed_at')
            ->whereNull('unsubscribed_at')
            ->orderBy('id')
            ->chunkById(100, function ($subscribers) use ($unit, $product, $productUrl, $imageUrl, &$sentSuccessfully): void {
                foreach ($subscribers as $subscriber) {
                    $unsubscribeUrl = URL::temporarySignedRoute(
                        'newsletter.unsubscribe',
                        now()->addYears(5),
                        ['subscriber' => $subscriber->getKey()],
                    );

                    try {
                        Mail::to($subscriber->email)->send(new NewProductUnitPosted(
                            productName: $product->name,
                            brandName: $product->brand->name,
                            categoryName: $product->category->name,
                            sku: $unit->sku ?? 'Unavailable',
                            condition: ProductUnit::CONDITIONS[$unit->condition] ?? $unit->condition,
                            price: Naira::format($unit->selling_price),
                            imageUrl: $imageUrl,
                            productUrl: $productUrl,
                            unsubscribeUrl: $unsubscribeUrl,
                        ));
                    } catch (Throwable $exception) {
                        report($exception);
                        $sentSuccessfully = false;
                    }
                }
            });

        if ($sentSuccessfully) {
            $unit->forceFill(['newsletter_notified_at' => now()])->saveQuietly();
        }
    }
}