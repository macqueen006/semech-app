<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class SubscriberController extends Controller
{
    /**
     * Display a listing of subscribers
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->input('search', '');
        $order = $request->input('order', 'desc');
        $limit = (int)$request->input('limit', 20);
        $statusFilter = $request->input('status', 'all');

        // Build query
        $query = NewsletterSubscriber::orderBy('id', $order);

        // Search
        if ($search) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        // Status Filter
        if ($statusFilter === 'active') {
            $query->active();
        } elseif ($statusFilter === 'unsubscribed') {
            $query->whereNotNull('unsubscribed_at');
        }

        // Get subscribers with proper pagination
        if ($limit == 0) {
            // Get all subscribers but create a paginator for consistency
            $allSubscribers = $query->get();
            $subscribers = new LengthAwarePaginator(
                $allSubscribers,
                $allSubscribers->count(),
                $allSubscribers->count(),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $subscribers = $query->paginate($limit);
        }

        // Get statistics
        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::active()->count(),
            'unsubscribed' => NewsletterSubscriber::whereNotNull('unsubscribed_at')->count(),
            'today' => NewsletterSubscriber::whereDate('subscribed_at', today())->count(),
            'this_week' => NewsletterSubscriber::whereBetween('subscribed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => NewsletterSubscriber::whereMonth('subscribed_at', now()->month)->whereYear('subscribed_at', now()->year)->count(),
        ];

        // Check if this is an AJAX request
        if ($request->ajax()) {
            $paginationHtml = '';

            // Only render pagination if limit is set
            if ($limit > 0 && $subscribers instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $paginationHtml = $subscribers->links()->render();
            }

            return response()->json([
                'success' => true,
                'html' => view('admin.subscribers.partials.subscribers-table', [
                    'subscribers' => $subscribers
                ])->render(),
                'pagination' => $paginationHtml,
                'stats' => $stats
            ]);
        }

        return view('admin.subscribers.index', compact(
            'subscribers',
            'stats',
            'search',
            'order',
            'limit',
            'statusFilter'
        ));
    }

    /**
     * Display the specified subscriber
     */
    public function show($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);

        return view('admin.subscribers.show', compact('subscriber'));
    }

    /**
     * Remove the specified subscriber
     */
    public function destroy(Request $request, $id)
    {
        try {
            $subscriber = NewsletterSubscriber::findOrFail($id);
            $email = $subscriber->email;
            $subscriber->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Subscriber '{$email}' deleted successfully!"
                ]);
            }

            return redirect()->route('admin.subscribers.index')
                ->with('success', "Subscriber '{$email}' deleted successfully!");
        } catch (\Exception $e) {
            \Log::error('Error deleting subscriber: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete subscriber. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Unable to delete subscriber. Please try again.');
        }
    }

    /**
     * Bulk delete subscribers
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:newsletter_subscribers,id'
        ]);

        try {
            $count = NewsletterSubscriber::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} subscriber(s) deleted successfully!"
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting subscribers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subscribers. Please try again.'
            ], 500);
        }
    }

    /**
     * Bulk resubscribe subscribers
     */
    public function bulkResubscribe(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:newsletter_subscribers,id'
        ]);

        try {
            $count = NewsletterSubscriber::whereIn('id', $request->ids)
                ->update([
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} subscriber(s) resubscribed successfully!"
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk resubscribing subscribers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resubscribe subscribers. Please try again.'
            ], 500);
        }
    }

    /**
     * Bulk unsubscribe subscribers
     */
    public function bulkUnsubscribe(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:newsletter_subscribers,id'
        ]);

        try {
            $count = NewsletterSubscriber::whereIn('id', $request->ids)
                ->whereNull('unsubscribed_at')
                ->update(['unsubscribed_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => "{$count} subscriber(s) unsubscribed successfully!"
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk unsubscribing subscribers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe subscribers. Please try again.'
            ], 500);
        }
    }

    /**
     * Export subscribers to CSV
     */
    public function export(Request $request)
    {
        // Don't allow AJAX requests for export
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Export must be accessed directly'
            ], 400);
        }

        try {
            $statusFilter = $request->input('status', 'all');

            $query = NewsletterSubscriber::query();

            if ($statusFilter === 'active') {
                $query->active();
            } elseif ($statusFilter === 'unsubscribed') {
                $query->whereNotNull('unsubscribed_at');
            }

            $subscribers = $query->get();

            // Create CSV content
            $csv = "Email,Status,Subscribed At,Unsubscribed At\n";

            foreach ($subscribers as $subscriber) {
                $status = $subscriber->isSubscribed() ? 'Active' : 'Unsubscribed';
                $subscribedAt = $subscriber->subscribed_at ? $subscriber->subscribed_at->format('Y-m-d H:i:s') : '';
                $unsubscribedAt = $subscriber->unsubscribed_at ? $subscriber->unsubscribed_at->format('Y-m-d H:i:s') : '';

                $csv .= "\"{$subscriber->email}\",\"{$status}\",\"{$subscribedAt}\",\"{$unsubscribedAt}\"\n";
            }

            $filename = 'subscribers_' . now()->format('Y-m-d_His') . '.csv';

            return response($csv, 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            \Log::error('Error exporting subscribers: ' . $e->getMessage());

            return back()->with('error', 'Failed to export subscribers. Please try again.');
        }
    }

    /**
     * Resubscribe a subscriber
     */
    public function resubscribe(Request $request, $id)
    {
        try {
            $subscriber = NewsletterSubscriber::findOrFail($id);

            // Check if already subscribed
            if ($subscriber->isSubscribed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscriber is already active.'
                ], 400);
            }

            $subscriber->update([
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscriber resubscribed successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error resubscribing subscriber: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resubscribe subscriber. Please try again.'
            ], 500);
        }
    }

    /**
     * Unsubscribe a subscriber
     */
    public function unsubscribe(Request $request, $id)
    {
        try {
            $subscriber = NewsletterSubscriber::findOrFail($id);

            // Check if already unsubscribed
            if ($subscriber->unsubscribed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscriber is already unsubscribed.'
                ], 400);
            }

            $subscriber->update([
                'unsubscribed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscriber unsubscribed successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error unsubscribing subscriber: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe subscriber. Please try again.'
            ], 500);
        }
    }

    /**
     * Regenerate subscriber token
     */
    public function regenerateToken(Request $request, $id)
    {
        try {
            $subscriber = NewsletterSubscriber::findOrFail($id);

            $newToken = Str::random(32);

            $subscriber->update([
                'token' => $newToken,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token regenerated successfully!',
                'token' => $subscriber->token
            ]);

        } catch (\Exception $e) {
            \Log::error('Error regenerating token: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate token. Please try again.'
            ], 500);
        }
    }

    /**
     * Get subscribers data for AJAX requests
     */
    public function getData(Request $request)
    {
        $search = $request->input('search', '');
        $order = $request->input('order', 'desc');
        $limit = (int)$request->input('limit', 20);
        $statusFilter = $request->input('status', 'all');

        $query = NewsletterSubscriber::orderBy('id', $order);

        if ($search) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        if ($statusFilter === 'active') {
            $query->active();
        } elseif ($statusFilter === 'unsubscribed') {
            $query->whereNotNull('unsubscribed_at');
        }

        $subscribers = $limit == 0 ? $query->get() : $query->paginate($limit);

        if ($subscribers instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return response()->json([
                'success' => true,
                'subscribers' => $subscribers->items(),
                'pagination' => [
                    'current_page' => $subscribers->currentPage(),
                    'last_page' => $subscribers->lastPage(),
                    'per_page' => $subscribers->perPage(),
                    'total' => $subscribers->total(),
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'subscribers' => $subscribers->toArray(),
            'pagination' => null
        ]);
    }

    /**
     * Get statistics
     */
    public function getStats()
    {
        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::active()->count(),
            'unsubscribed' => NewsletterSubscriber::whereNotNull('unsubscribed_at')->count(),
            'today' => NewsletterSubscriber::whereDate('subscribed_at', today())->count(),
            'this_week' => NewsletterSubscriber::whereBetween('subscribed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => NewsletterSubscriber::whereMonth('subscribed_at', now()->month)->whereYear('subscribed_at', now()->year)->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
