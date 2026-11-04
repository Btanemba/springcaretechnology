<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\EmailSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;
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
            return back()->with('newsletter_status', 'If this address is subscribed, it will stay on the new-device list.')
                ->with('newsletter_status_type', 'info')
                ->with('newsletter_status_title', 'Subscription status');
        }

        $subscriber->forceFill([
            'confirmed_at' => null,
            'unsubscribed_at' => null,
        ])->save();

        try {
            Mail::to($subscriber->email)->send(new ConfirmNewsletterSubscription($subscriber));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('newsletter_status', 'We could not send a confirmation email right now. Please try again later.')
                ->with('newsletter_status_type', 'error')
                ->with('newsletter_status_title', 'Email not sent');
        }

        return back()->with('newsletter_status', 'Please check your inbox to confirm your subscription.')
            ->with('newsletter_status_type', 'success')
            ->with('newsletter_status_title', 'Check your inbox');
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
            ->with('newsletter_status', 'Your email preferences have been updated.')
            ->with('newsletter_status_type', 'success')
            ->with('newsletter_status_title', 'Subscription confirmed');
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
            ->with('newsletter_status', 'You have been unsubscribed from new-device emails.')
            ->with('newsletter_status_type', 'success')
            ->with('newsletter_status_title', 'Unsubscribed');
    }
}