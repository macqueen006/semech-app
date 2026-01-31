<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    public function getComments(
        ?string $terms = null,
        string $order = 'desc',
        ?array $userIds = null,
        int $limit = 20
    ) {
        $query = Comment::query();

        // Only get top-level comments (not replies)
        $query->whereNull('parent_id');

        // Check permissions and apply user filters
        if (Auth::user()->hasPermissionTo('comment-super-list')) {
            if ($userIds) {
                $query->join('posts', 'posts.id', '=', 'comments.post_id')
                    ->whereIn('posts.user_id', $userIds)
                    ->select('comments.*');
            }
        } else {
            $query->join('posts', 'posts.id', '=', 'comments.post_id')
                ->where('posts.user_id', Auth::id())
                ->select('comments.*');
        }

        // Apply search terms
        if ($terms) {
            $keywords = explode(' ', $terms);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('comments.name', 'like', '%' . $keyword . '%')
                        ->orWhere('comments.body', 'like', '%' . $keyword . '%');
                }
            });
        }

        // Eager load replies with nested replies and users
        $query->with(['replies.replies.replies.user', 'post', 'user']);

        // Apply ordering
        $query->orderBy('comments.id', $order);

        // Apply pagination or get all
        if ($limit === 0) {
            return $query->get();
        }

        return $query->paginate($limit);
    }

    public function createComment(array $data, string $postSlug): Comment
    {
        $post = Post::where('slug', $postSlug)->firstOrFail();

        $commentData = [
            'body' => $data['body'],
            'post_id' => $post->id,
        ];

        // If user is authenticated, use their ID and auto-approve
        if (Auth::check()) {
            $commentData['user_id'] = Auth::id();
            $commentData['name'] = null;
            $commentData['is_approved'] = true; // Auto-approve authenticated users
        } else {
            // Guest comment - require approval
            if (empty($data['name'])) {
                abort(400, 'Name is required for guest comments');
            }
            $commentData['name'] = $data['name'];
            $commentData['user_id'] = null;
            $commentData['is_approved'] = false; // Require approval for guests
        }

        $comment = Comment::create($commentData);

        // Notify post owner
        $this->notifyPostOwner($post, $postSlug, $comment);

        return $comment;
    }

    public function createReply(array $data, string $postSlug, int $parentId): Comment
    {
        $post = Post::where('slug', $postSlug)->firstOrFail();
        $parentComment = Comment::findOrFail($parentId);

        // Ensure parent comment belongs to same post
        if ($parentComment->post_id !== $post->id) {
            abort(400, 'Parent comment does not belong to this post');
        }

        // Check max depth (limit to 4 levels: 0, 1, 2, 3)
        if ($parentComment->getDepth() >= 3) {
            abort(400, 'Maximum nesting depth reached');
        }

        $commentData = [
            'body' => $data['body'],
            'post_id' => $post->id,
            'parent_id' => $parentId,
        ];

        // If user is authenticated, use their ID and auto-approve
        if (Auth::check()) {
            $commentData['user_id'] = Auth::id();
            $commentData['name'] = null;
            $commentData['is_approved'] = true; // Auto-approve authenticated users
        } else {
            // Guest comment - require approval
            if (empty($data['name'])) {
                abort(400, 'Name is required for guest comments');
            }
            $commentData['name'] = $data['name'];
            $commentData['user_id'] = null;
            $commentData['is_approved'] = false; // Require approval for guests
        }

        $comment = Comment::create($commentData);

        // Notify post owner and parent comment author
        $this->notifyPostOwner($post, $postSlug, $comment);
        $this->notifyParentCommentAuthor($parentComment, $postSlug);

        return $comment;
    }

    public function updateComment(int $commentId, array $data): Comment
    {
        $comment = Comment::findOrFail($commentId);
        $post = Post::findOrFail($comment->post_id);

        $this->checkUserPermission($post, $comment);

        // Only update body - name and user_id shouldn't change
        $comment->update([
            'body' => $data['body']
        ]);

        return $comment;
    }

    public function deleteComment(int $commentId): bool
    {
        $comment = Comment::with('post')->findOrFail($commentId);

        // Check permission
        $this->checkUserPermission($comment->post, $comment);

        // Delete all replies recursively
        $this->deleteCommentAndReplies($comment);

        return true;
    }

    /**
     * Recursively delete comment and all its replies
     */
    private function deleteCommentAndReplies(Comment $comment): void
    {
        // Load all replies
        $replies = Comment::where('parent_id', $comment->id)->get();

        // Recursively delete each reply
        foreach ($replies as $reply) {
            $this->deleteCommentAndReplies($reply);
        }

        // Delete the comment itself
        $comment->delete();
    }

    public function getComment(int $commentId): Comment
    {
        $comment = Comment::findOrFail($commentId);
        $post = Post::findOrFail($comment->post_id);

        $this->checkUserPermission($post, $comment);

        return $comment;
    }

    public function getAllUsers(): Collection
    {
        return User::all();
    }

    public function checkUserPermission(Post $post, ?Comment $comment = null): void
    {
        // Must be authenticated
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        // Super users can do anything
        if (Auth::user()->hasPermissionTo('comment-super-list')) {
            return;
        }

        // Post owner can manage all comments on their post
        if ($post->user_id === Auth::id()) {
            return;
        }

        // Comment owner can manage their own comment
        if ($comment && $comment->user_id === Auth::id()) {
            return;
        }

        abort(403, 'You are not authorized to perform this action');
    }

    private function notifyPostOwner(Post $post, string $postSlug, Comment $comment): void
    {
        // Notify if guest or different user
        if (!Auth::check() || Auth::id() !== $post->user_id) {
            $message = $comment->is_approved
                ? 'A new comment has appeared on your post.'
                : 'A new comment is awaiting your approval.';

            $post->user->notify(new CommentNotification(
                'INFO',
                $message,
                "/post/$postSlug"
            ));
        }
    }

    public function approveComment(int $commentId): Comment
    {
        // Eager load the post relationship
        $comment = Comment::with('post')->findOrFail($commentId);

        // Must be authenticated
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        // Check if user has permission to approve
        // Only post owner or super admin can approve
        $isPostOwner = $comment->post->user_id === Auth::id();
        $hasSuperPermission = Auth::user()->hasPermissionTo('comment-super-list');

        if (!$isPostOwner && !$hasSuperPermission) {
            abort(403, 'You are not authorized to approve this comment');
        }

        // Update the comment
        $comment->is_approved = true;
        $comment->save();

        // Optionally notify the commenter that their comment was approved
        if ($comment->user_id && $comment->user) {
            $comment->user->notify(new CommentNotification(
                'SUCCESS',
                'Your comment has been approved!',
                "/post/" . $comment->post->slug
            ));
        }

        return $comment->fresh(['post', 'user']);
    }

    private function notifyParentCommentAuthor(Comment $parentComment, string $postSlug): void
    {
        // Only notify if parent commenter is a registered user
        if ($parentComment->user_id) {
            // Don't notify if replying to yourself
            if (!Auth::check() || Auth::id() !== $parentComment->user_id) {
                $parentComment->user->notify(new CommentNotification(
                    'INFO',
                    'Someone replied to your comment.',
                    "/post/$postSlug"
                ));
            }
        }
    }

    private function isSpam(string $body): bool
    {
        // Check for excessive links
        if (substr_count(strtolower($body), 'http') > 2) {
            return true;
        }

        // Check for repeated characters
        if (preg_match('/(.)\1{10,}/', $body)) {
            return true;
        }

        return false;
    }
}
