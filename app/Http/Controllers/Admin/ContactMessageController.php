<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of contact messages
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'all');
        $page = $request->input('page', 1);

        // Build query
        $query = ContactMessage::query()->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // Get messages with pagination
        $messages = $query->paginate(20);

        // Get counts
        $counts = [
            'all' => ContactMessage::count(),
            'unread' => ContactMessage::where('status', 'unread')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
        ];

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.contact.partials.messages-list', compact('messages'))->render(),
                'pagination' => $messages->links()->render(),
                'counts' => $counts
            ]);
        }

        return view('admin.contact.index', compact('messages', 'counts', 'statusFilter'));
    }

    /**
     * Display the specified contact message
     */
    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);

        // Mark as read if unread
        if ($message->status === 'unread') {
            $message->markAsRead();
        }

        return view('admin.contact.show', compact('message'));
    }

    /**
     * Mark message as replied
     */
    public function markAsReplied(Request $request, $id)
    {
        try {
            $message = ContactMessage::findOrFail($id);

            $message->markAsReplied(auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Marked as replied',
                'status' => $message->status,
                'replied_at' => $message->replied_at->format('M d, Y H:i'),
                'replied_by' => $message->replier->firstname . ' ' . $message->replier->lastname
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking message as replied: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as replied. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete the specified contact message
     */
    public function destroy(Request $request, $id)
    {
        try {
            $message = ContactMessage::findOrFail($id);
            $message->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message deleted successfully!'
                ]);
            }

            return redirect()->route('admin.contact.index')
                ->with('success', 'Message deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Error deleting message: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete message. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Unable to delete message. Please try again.');
        }
    }
}
