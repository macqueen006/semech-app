<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ], [
            'email.required' => 'Please enter your email address to subscribe.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already subscribed to our newsletter.',
        ]);

        NewsletterSubscriber::create([
            'email' => $request->email,
            'token' => \Str::random(32),
            'subscribed_at' => now(),
        ]);

        app(AnalyticsService::class)->clearCache();

        return back()->with('newsletter_success', 'Thank you for subscribing! Check your email for confirmation.');
    }

    public function subscribeFooter(Request $request)
    {
        $request->validate([
            'footer_email' => 'required|email|unique:newsletter_subscribers,email'
        ], [
            'footer_email.required' => 'Please enter your email address to subscribe.',
            'footer_email.email' => 'Please enter a valid email address.',
            'footer_email.unique' => 'This email is already subscribed to our newsletter.',
        ]);

        NewsletterSubscriber::create([
            'email' => $request->footer_email,
            'token' => \Str::random(32),
            'subscribed_at' => now(),
        ]);

        app(AnalyticsService::class)->clearCache();

        return back()->with('newsletter_success', 'Thank you for subscribing! Check your email for confirmation.');
    }
}
