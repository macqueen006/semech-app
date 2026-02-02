<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Display a listing of comments
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $terms = $request->input('q', '');
        $order = $request->input('order', 'desc');
        $limit = (int) $request->input('limit', 20);
        $selectedUserIds = $request->input('users', []);

        // Handle comma-separated user IDs
        if (is_string($selectedUserIds)) {
            $selectedUserIds = explode(',', $selectedUserIds);
        }

        // Get comments using the service
        $comments = $this->commentService->getComments(
            $terms,
            $order,
            $selectedUserIds ?: null,
            $limit
        );

        // Get all users for filter
        $users = $this->commentService->getAllUsers();

        // Get selected users for display
        $selectedUsers = $selectedUserIds
            ? User::whereIn('id', $selectedUserIds)->get()
            : collect();

        // Check if this is an AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.comments.partials.comments-list', [
                    'comments' => $comments
                ])->render(),
                'pagination' => $comments->links()->render()
            ]);
        }

        return view('admin.comments.index', compact(
            'comments',
            'users',
            'selectedUsers',
            'terms',
            'order',
            'limit',
            'selectedUserIds'
        ));
    }

    /**
     * Show the form for editing a comment
     */
    public function edit($id)
    {
        $comment = $this->commentService->getComment($id);

        return view('admin.comments.edit', compact('comment'));
    }

    /**
     * Update the specified comment
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        try {
            $this->commentService->updateComment($id, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment has been updated'
                ]);
            }

            return redirect()->route('admin.comments.index')
                ->with('message', 'Comment has been updated');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to update comment'
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Unable to update comment');
        }
    }

    /**
     * Remove the specified comment
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->commentService->deleteComment($id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment has been deleted'
                ]);
            }

            return redirect()->route('admin.comments.index')
                ->with('message', 'Comment has been deleted');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete the comment'
                ], 500);
            }

            return back()->with('error', 'Unable to delete the comment');
        }
    }

    /**
     * Get comments data for AJAX requests (filtering, searching, etc.)
     */
    public function getData(Request $request)
    {
        $terms = $request->input('terms', '');
        $order = $request->input('order', 'desc');
        $limit = (int) $request->input('limit', 20);
        $selectedUserIds = $request->input('selectedUserIds', []);

        $comments = $this->commentService->getComments(
            $terms,
            $order,
            $selectedUserIds ?: null,
            $limit
        );

        return response()->json([
            'success' => true,
            'comments' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
            'html' => view('admin.comments.partials.comments-list', [
                'comments' => $comments
            ])->render(),
            'pagination_html' => $comments->links()->render()
        ]);
    }

    /**
     * Get the count of comments (for stats)
     */
    public function getCount(Request $request)
    {
        $terms = $request->input('terms', '');
        $selectedUserIds = $request->input('selectedUserIds', []);

        $query = Comment::query();

        if ($terms) {
            $query->where(function ($q) use ($terms) {
                $q->where('name', 'like', '%' . $terms . '%')
                    ->orWhere('body', 'like', '%' . $terms . '%');
            });
        }

        if ($selectedUserIds) {
            $query->whereHas('post', function ($q) use ($selectedUserIds) {
                $q->whereIn('user_id', $selectedUserIds);
            });
        }

        $count = $query->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
}
