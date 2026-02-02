<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoryPost;
use App\Models\Post;
use App\Models\SavedPost;
use Illuminate\Http\Request;

class PostHistoryController extends Controller
{
    public function index($id)
    {
        // Get current post
        $currentPost = Post::with('changeUser')->findOrFail($id);

        // Check permission
        if ($currentPost->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            abort(403, 'You do not have permission to view this post history.');
        }

        // Get history posts (exclude autosave drafts with additional_info = 2)
        $historyPosts = HistoryPost::with('changeUser')
            ->where('post_id', $id)
            ->where('additional_info', '!=', 2) // Exclude autosave drafts
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.history.index', compact('currentPost', 'historyPosts', 'id'));
    }
    public function show($id, $historyId)
    {
        $currentPost = Post::with('changeUser')->findOrFail($id);

        // Check permission
        if ($currentPost->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            abort(403);
        }

        // Get history posts for dropdown
        $historyPosts = HistoryPost::with('changeUser')
            ->where('post_id', $id)
            ->where('additional_info', '!=', 2) // Exclude autosave drafts
            ->orderBy('created_at', 'desc')
            ->get();

        // If viewing current version
        if ($historyId === 'current') {
            $post = $currentPost;
            $isCurrent = true;
        } else {
            $post = HistoryPost::with('changeUser', 'category')->findOrFail($historyId);
            $isCurrent = false;
        }

        return view('admin.history.show', compact('currentPost', 'post', 'historyPosts', 'isCurrent', 'id', 'historyId'));
    }

    public function revert(Request $request, $id, $historyId)
    {
        $currentPost = Post::findOrFail($id);

        // Check permission
        if ($currentPost->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($historyId === 'current') {
            return response()->json([
                'success' => false,
                'message' => 'You cannot restore the current version'
            ], 400);
        }

        $historyPost = HistoryPost::findOrFail($historyId);

        // Check if this version is already the current one
        if ($currentPost->title === $historyPost->title &&
            $currentPost->body === $historyPost->body &&
            $currentPost->excerpt === $historyPost->excerpt) {
            return response()->json([
                'success' => false,
                'message' => 'This version is already restored'
            ], 400);
        }

        try {
            // Save current version to history before reverting
            \App\Models\HistoryPost::create([
                'post_id' => $currentPost->id,
                'title' => $currentPost->title,
                'excerpt' => $currentPost->excerpt,
                'body' => $currentPost->body,
                'image_path' => $currentPost->image_path,
                'slug' => $currentPost->slug,
                'is_published' => $currentPost->is_published,
                'additional_info' => $currentPost->additional_info,
                'category_id' => $currentPost->category_id,
                'read_time' => $currentPost->read_time,
                'change_user_id' => $currentPost->change_user_id,
                'changelog' => 'Saved before reverting to history',
                'created_at' => now(),
                'updated_at' => now(),
                'scheduled_at' => $currentPost->scheduled_at,
                'expires_at' => $currentPost->expires_at,
                'meta_title' => $currentPost->meta_title,
                'meta_description' => $currentPost->meta_description,
                'focus_keyword' => $currentPost->focus_keyword,
                'image_alt' => $currentPost->image_alt,
                'og_title' => $currentPost->og_title,
                'og_description' => $currentPost->og_description,
                'og_image' => $currentPost->og_image,
                'twitter_title' => $currentPost->twitter_title,
                'twitter_description' => $currentPost->twitter_description,
                'twitter_image' => $currentPost->twitter_image,
            ]);

            // Restore from history
            $currentPost->update([
                'title' => $historyPost->title,
                'excerpt' => $historyPost->excerpt,
                'body' => $historyPost->body,
                'image_path' => $historyPost->image_path,
                'slug' => \Illuminate\Support\Str::slug($historyPost->title),
                'is_published' => $historyPost->is_published,
                'category_id' => $historyPost->category_id,
                'read_time' => $historyPost->read_time,
                'change_user_id' => auth()->id(),
                'changelog' => 'Restored from history version: ' . $historyPost->created_at->format('Y-m-d H:i:s'),
                'scheduled_at' => $historyPost->scheduled_at,
                'expires_at' => $historyPost->expires_at,
                'meta_title' => $historyPost->meta_title,
                'meta_description' => $historyPost->meta_description,
                'focus_keyword' => $historyPost->focus_keyword,
                'image_alt' => $historyPost->image_alt,
                'og_title' => $historyPost->og_title,
                'og_description' => $historyPost->og_description,
                'og_image' => $historyPost->og_image,
                'twitter_title' => $historyPost->twitter_title,
                'twitter_description' => $historyPost->twitter_description,
                'twitter_image' => $historyPost->twitter_image,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'The post has been restored from history',
                'redirect' => route('admin.posts.edit', $id)
            ]);

        } catch (\Exception $e) {
            \Log::error('History revert failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while restoring the post'
            ], 500);
        }
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
