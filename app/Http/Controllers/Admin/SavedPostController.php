<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedPost;
use Illuminate\Http\Request;

class SavedPostController extends Controller
{
    public function index()
    {
        $posts = SavedPost::where('user_id', auth()->id())
            ->orderBy('id', 'DESC')
            ->get();

        return view('admin.saved-posts.index', compact('posts'));
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('post-create')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete saved posts.'
            ], 403);
        }

        try {
            $savedPost = SavedPost::findOrFail($id);

            // Check authorization
            if ($savedPost->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this saved post.'
                ], 403);
            }

            $savedPost->delete();

            return response()->json([
                'success' => true,
                'message' => 'Saved post deleted successfully!'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Saved post not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Saved post deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the saved post.'
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('post-create')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete saved posts.'
            ], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:saved_posts,id'
        ]);

        $deleted = SavedPost::whereIn('id', $request->ids)
            ->where('user_id', auth()->id()) // ✅ Security: only delete own drafts
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} saved post(s) deleted successfully!"
        ]);
    }
}
