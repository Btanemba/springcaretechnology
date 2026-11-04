<?php

namespace App\Mail;

use App\Models\EmailSubscriber;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class ConfirmNewsletterSubscription extends Mailable
{
    public string $confirmationUrl;

    public function __construct(public EmailSubscriber $subscriber)
    {
        $this->confirmationUrl = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addDay(),
            ['subscriber' => $subscriber->getKey()],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirm new-device email updates');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-confirmation',
            with: ['confirmationUrl' => $this->confirmationUrl],
        );
    }
}