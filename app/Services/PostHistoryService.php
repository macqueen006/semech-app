<?php

namespace App\Services;
use App\Models\HistoryPost;
use App\Models\Post;
use App\Notifications\PostNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class PostHistoryService
{
    public function getHistoryPosts(int $postId): Collection
    {
        return HistoryPost::where('post_id', $postId)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function getCurrentPost(int $postId): Post
    {
        return Post::findOrFail($postId);
    }

    public function getHistoryPost(int $postId, mixed $historyId)
    {
        if ($historyId === 'current') {
            return Post::with('category', 'user', 'changeUser')
                ->findOrFail($postId);
        }

        return HistoryPost::with('category', 'changeUser')
            ->findOrFail($historyId);
    }

    public function revertToHistory(int $postId, int $historyId): bool
    {
        $post = Post::findOrFail($postId);
        $historyPost = HistoryPost::findOrFail($historyId);

        // Can't revert to a revert
        if ($historyPost->additional_info === 2) {
            return false;
        }

        // Save current version to history before reverting
        $this->saveToHistory($post);

        // Restore from history (INCLUDING scheduling dates)
        $post->update([
            'title' => $historyPost->title,
            'excerpt' => $historyPost->excerpt,
            'body' => $historyPost->body,
            'is_published' => $historyPost->is_published,
            'image_path' => $historyPost->image_path,
            'slug' => $historyPost->slug,
            'additional_info' => 1,
            'category_id' => $historyPost->category_id,
            'read_time' => $historyPost->read_time,
            'change_user_id' => Auth::id(),
            'changelog' => null,
            'scheduled_at' => $historyPost->scheduled_at,
            'expires_at' => $historyPost->expires_at,

            // ADD SEO FIELDS
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

        // Notify post owner if different user
        $this->notifyPostOwner($post);

        return true;
    }

    public function saveToHistory(Post $post): HistoryPost
    {
        return HistoryPost::create([
            'post_id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'image_path' => $post->image_path,
            'slug' => $post->slug,
            'is_published' => $post->is_published,
            'additional_info' => $post->additional_info,
            'category_id' => $post->category_id,
            'read_time' => $post->read_time,
            'change_user_id' => $post->change_user_id,
            'changelog' => $post->changelog,
            'scheduled_at' => $post->scheduled_at,
            'expires_at' => $post->expires_at,
            // ADD SEO FIELDS
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'focus_keyword' => $post->focus_keyword,
            'image_alt' => $post->image_alt,
            'og_title' => $post->og_title,
            'og_description' => $post->og_description,
            'og_image' => $post->og_image,
            'twitter_title' => $post->twitter_title,
            'twitter_description' => $post->twitter_description,
            'twitter_image' => $post->twitter_image,
            'created_at' => $post->updated_at,
            'updated_at' => $post->updated_at,
        ]);
    }

    public function checkUserPermission(Post $post): void
    {
        if ($post->user_id != Auth::id() && !Auth::user()->hasPermissionTo('post-super-list')) {
            abort(403);
        }
    }

    private function notifyPostOwner(Post $post): void
    {
        if (Auth::id() !== $post->user_id) {
            $currentUser = Auth::user();
            $post->user->notify(new PostNotification(
                'INFO',
                "Nastąpiła edycja posta przez {$currentUser->firstname} {$currentUser->lastname}. Post został przywrócony z historii.",
                "/dashboard/posts/{$post->id}/edit/history/current/show"
            ));
        }
    }
}
