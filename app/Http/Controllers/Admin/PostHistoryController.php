<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedPost;
use Illuminate\Http\Request;

class PostHistoryController extends Controller
{
    public function index()
    {

    }
    public function show()
    {

    }

    public function deleteDraft($id)
    {
        if (!auth()->user()->can('post-create')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete drafts.'
            ], 403);
        }

        try {
            $savedPost = SavedPost::findOrFail($id);

            // Check if the user owns this draft
            if ($savedPost->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this draft.'
                ], 403);
            }

            $savedPost->delete();

            return response()->json([
                'success' => true,
                'message' => 'Draft deleted successfully!'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Draft deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the draft.'
            ], 500);
        }
    }
}
