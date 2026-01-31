<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        // Rate limiting: 3 messages per hour per IP
        $key = 'contact-form:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Too many attempts. Please try again in {$seconds} seconds.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'body' => 'required|string|min:10|max:5000',
        ]);

        // Store message in database
        $contactMessage = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'body' => $validated['body'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'unread',
        ]);

        // Notify administrators
        $admins = User::role('Admin')->get();

        /*foreach ($admins as $admin) {
            $admin->notify(new ContactNotification(
                'NEW_MESSAGE',
                "New contact message from {$validated['name']}",
                route('admin.contact.show', $contactMessage->id),
                $validated
            ));
        }*/

        // Increment rate limiter
        RateLimiter::hit($key, 3600); // 1 hour

        return back()->with('success', 'Thank you! Your submission has been received!');
    }
}
