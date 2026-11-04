<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewProductUnitPosted extends Mailable
{
    public function __construct(
        public string $productName,
        public string $brandName,
        public string $categoryName,
        public string $sku,
        public string $condition,
        public string $price,
        public string $imageUrl,
        public string $productUrl,
        public string $unsubscribeUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'A new device has been posted');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-product-unit');
    }
}