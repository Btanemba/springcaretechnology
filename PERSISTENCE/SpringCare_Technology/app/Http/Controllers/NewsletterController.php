<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\EmailSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:254'],
        ]);
        $email = strtolower(trim($validated['email']));
        $subscriber = EmailSubscriber::firstOrNew(['email' => $email]);

        if ($subscriber->confirmed_at && ! $subscriber->unsubscribed_at) {
            return back()->with('newsletter_status', 'If this address is subscribed, it will stay on the new-device list.');
        }

        $subscriber->forceFill([
            'confirmed_at' => null,
            'unsubscribed_at' => null,
        ])->save();

        Mail::to($subscriber->email)->queue(new ConfirmNewsletterSubscription($subscriber));

        return back()->with('newsletter_status', 'Please check your inbox to confirm your subscription.');
    }

    public function showConfirmation(EmailSubscriber $subscriber): View
    {
        return view('storefront.newsletter.confirm', compact('subscriber'));
    }

    public function confirm(EmailSubscriber $subscriber): RedirectResponse
    {
        if (! $subscriber->confirmed_at && ! $subscriber->unsubscribed_at) {
            $subscriber->forceFill(['confirmed_at' => now()])->save();
        }

        return redirect()->route('storefront.home')
            ->with('newsletter_status', 'Your email preferences have been updated.');
    }

    public function showUnsubscribe(EmailSubscriber $subscriber): View
    {
        return view('storefront.newsletter.unsubscribe', compact('subscriber'));
    }

    public function unsubscribe(EmailSubscriber $subscriber): RedirectResponse
    {
        if (! $subscriber->unsubscribed_at) {
            $subscriber->forceFill(['unsubscribed_at' => now()])->save();
        }

        return redirect()->route('storefront.home')
            ->with('newsletter_status', 'You have been unsubscribed from new-device emails.');
    }
}