<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HighlightPost;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\User;
use App\Models\HistoryPost;
use App\Notifications\PostNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $order = $request->input('order', 'desc');
        $limit = $request->input('limit', 20);
        $selectedUsers = $request->input('users', []);
        $selectedCategories = $request->input('categories', []);
        $highlightFilter = $request->input('highlight', []);
        $statusFilter = $request->input('status', []);

        // Build query
        $query = Post::with(['category', 'user'])
            ->select('posts.*', \DB::raw('(SELECT COUNT(*) FROM highlight_posts WHERE post_id = posts.id) > 0 AS is_highlighted'))
            ->orderBy('id', $order);

        // Permission check
        if (!auth()->user()->hasPermissionTo('post-super-list')) {
            $query->where('user_id', auth()->id());
        } else {
            if (!empty($selectedUsers)) {
                $query->whereIn('user_id', $selectedUsers);
            }
        }

        // Category filter
        if (!empty($selectedCategories)) {
            $query->whereIn('category_id', $selectedCategories);
        }

        // Highlight filter
        if (!empty($highlightFilter)) {
            if (count($highlightFilter) === 1) {
                if (in_array('1', $highlightFilter)) {
                    $query->whereHas('highlightPosts');
                }
                if (in_array('0', $highlightFilter)) {
                    $query->doesntHave('highlightPosts');
                }
            }
        }

        // Search filter
        if ($search) {
            $keywords = explode(' ', $search);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'like', '%' . $keyword . '%');
                }
            });
        }

        // Status filter
        if (!empty($statusFilter)) {
            $query->where(function($q) use ($statusFilter) {
                foreach ($statusFilter as $status) {
                    if ($status === 'draft') {
                        $q->orWhere('is_published', false);
                    } elseif ($status === 'scheduled') {
                        $q->orWhere(function($sq) {
                            $sq->where('is_published', true)
                                ->whereNotNull('scheduled_at')
                                ->where('scheduled_at', '>', now());
                        });
                    } elseif ($status === 'published') {
                        $q->orWhere(function($sq) {
                            $sq->where('is_published', true)
                                ->where(function($ssq) {
                                    $ssq->whereNull('scheduled_at')
                                        ->orWhere('scheduled_at', '<=', now());
                                });
                        });
                    }
                }
            });
        }

        $posts = $limit == 0 ? $query->get() : $query->paginate($limit);

        // Get filter options
        $users = User::withCount('posts')->get();

        if (auth()->user()->hasPermissionTo('post-super-list')) {
            $categories = Category::withCount('posts')->get();
        } else {
            $categories = Category::withCount(['posts' => function ($query) {
                $query->where('user_id', auth()->id());
            }])->get();
        }

        $countPosts = auth()->user()->hasPermissionTo('post-super-list')
            ? Post::count()
            : auth()->user()->posts()->count();

        $countHighlighted = HighlightPost::count();

        return view('admin.posts.index', compact(
            'posts',
            'users',
            'categories',
            'countPosts',
            'countHighlighted',
            'search',
            'order',
            'limit',
            'selectedUsers',
            'selectedCategories',
            'highlightFilter',
            'statusFilter'
        ));
    }

    public function toggleHighlight(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('post-highlight')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to highlight posts.'], 403);
        }

        $post = Post::findOrFail($request->post_id);
        $highlighted = HighlightPost::where('post_id', $request->post_id)->first();

        if ($highlighted) {
            $highlighted->delete();
            ActivityLogger::logPostUnhighlight($post, auth()->user());
            return response()->json(['success' => true, 'message' => 'Post removed from highlights.', 'highlighted' => false]);
        } else {
            if (HighlightPost::count() >= 3) {
                return response()->json(['success' => false, 'message' => 'Maximum of 3 highlighted posts allowed.'], 400);
            }

            HighlightPost::create(['post_id' => $post->id]);

            if (auth()->id() !== $post->user_id) {
                $post->user->notify(new PostNotification('INFO', 'Wyróźniono post.', "/post/$post->slug"));
            }
            ActivityLogger::logPostHighlight($post, auth()->user());
            return response()->json(['success' => true, 'message' => 'Post highlighted successfully.', 'highlighted' => true]);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('post-delete')) {
            abort(403);
        }

        $post = Post::findOrFail($id);

        if ($post->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            abort(403);
        }

        if (auth()->id() !== $post->user_id) {
            $post->user->notify(new PostNotification('INFO', 'The post was deleted by '.auth()->user()->firstname.' '. auth()->user()->lastname. '.'));
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted successfully!']);
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('post-delete')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete posts.'], 403);
        }

        $posts = Post::whereIn('id', $request->ids)->get();

        foreach ($posts as $post) {
            if ($post->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
                continue;
            }

            if (auth()->id() !== $post->user_id) {
                $post->user->notify(new PostNotification('INFO', 'The post was deleted by '.auth()->user()->firstname.' '. auth()->user()->lastname. '.'));
            }

            $post->delete();
        }

        $count = count($request->ids);
        ActivityLogger::logBulkDelete('posts', $count, auth()->user(), $request->ids);

        return response()->json(['success' => true, 'message' => "{$count} post(s) deleted successfully!"]);
    }

    public function bulkPublish(Request $request)
    {
        if (!auth()->user()->can('post-edit')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to publish posts.'], 403);
        }

        $updated = Post::whereIn('id', $request->ids)
            ->when(!auth()->user()->hasPermissionTo('post-super-list'), function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->update(['is_published' => true]);

        ActivityLogger::logBulkUpdate('posts', $updated, auth()->user(), ['action' => 'published', 'ids' => $request->ids]);

        return response()->json(['success' => true, 'message' => "{$updated} post(s) published successfully!"]);
    }

    public function bulkUnpublish(Request $request)
    {
        if (!auth()->user()->can('post-edit')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to unpublish posts.'], 403);
        }

        $updated = Post::whereIn('id', $request->ids)
            ->when(!auth()->user()->hasPermissionTo('post-super-list'), function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->update(['is_published' => false]);

        return response()->json(['success' => true, 'message' => "{$updated} post(s) unpublished successfully!"]);
    }

    public function bulkChangeCategory(Request $request)
    {
        if (!auth()->user()->can('post-edit')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to edit posts.'], 403);
        }

        if (!$request->category_id) {
            return response()->json(['success' => false, 'message' => 'Please select a category.'], 400);
        }

        $updated = Post::whereIn('id', $request->ids)
            ->when(!auth()->user()->hasPermissionTo('post-super-list'), function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->update(['category_id' => $request->category_id]);

        $category = Category::find($request->category_id);

        ActivityLogger::logBulkUpdate('posts', $updated, auth()->user(), [
            'action' => 'category changed',
            'new_category' => $category->name,
            'ids' => $request->ids
        ]);

        return response()->json(['success' => true, 'message' => "{$updated} post(s) moved to category '{$category->name}' successfully!"]);
    }

    public function bulkHighlight(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('post-highlight')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to highlight posts.'], 403);
        }

        $currentHighlighted = HighlightPost::count();
        $canHighlight = 3 - $currentHighlighted;

        if ($canHighlight <= 0) {
            return response()->json(['success' => false, 'message' => 'Maximum of 3 highlighted posts allowed.'], 400);
        }

        $toHighlight = array_slice($request->ids, 0, $canHighlight);
        $highlighted = 0;

        foreach ($toHighlight as $postId) {
            $exists = HighlightPost::where('post_id', $postId)->exists();
            if (!$exists) {
                HighlightPost::create(['post_id' => $postId]);
                $highlighted++;

                $post = Post::find($postId);
                if ($post && auth()->id() !== $post->user_id) {
                    $post->user->notify(new PostNotification('INFO', 'Wyróźniono post.', "/post/{$post->slug}"));
                }
            }
        }

        return response()->json(['success' => true, 'message' => "{$highlighted} post(s) highlighted successfully!"]);
    }

    public function bulkRemoveHighlight(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('post-highlight')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to manage highlights.'], 403);
        }

        $removed = HighlightPost::whereIn('post_id', $request->ids)->delete();

        return response()->json(['success' => true, 'message' => "{$removed} post(s) removed from highlights successfully!"]);
    }

    public function create(Request $request)
    {
        if (!auth()->check() || !auth()->user()->can('post-create')) {
            abort(403);
        }

        $edit = $request->query('edit');
        $new = $request->query('new');

        // If editing a draft
        if ($edit) {
            $savedPost = SavedPost::find($edit);

            if (!$savedPost) {
                abort(404);
            }

            if ($savedPost->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
                abort(403);
            }

            $categories = Category::orderBy('name')->get();

            return view('admin.posts.create', [
                'savedPost' => $savedPost,
                'categories' => $categories,
                'showSavedPosts' => false,
            ]);
        }

        // If creating new with ?new parameter
        if ($new !== null) {
            $categories = Category::orderBy('name')->get();

            return view('admin.posts.create', [
                'savedPost' => null,
                'categories' => $categories,
                'showSavedPosts' => false,
            ]);
        }

        // Check for saved posts
        $hasSavedPosts = SavedPost::where('user_id', auth()->id())->exists();

        if ($hasSavedPosts) {
            $savedPosts = SavedPost::where('user_id', auth()->id())
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.posts.create', [
                'savedPost' => null,
                'categories' => Category::orderBy('name')->get(),
                'savedPosts' => $savedPosts,
                'showSavedPosts' => true,
            ]);
        }

        $categories = Category::orderBy('name')->get();

        return view('admin.posts.create', [
            'savedPost' => null,
            'categories' => $categories,
            'showSavedPosts' => false,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('post-create')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create posts.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'title' => 'required|max:255|unique:posts,title',
                'excerpt' => 'required|max:510',
                'body' => 'required',
                'category_id' => 'required|integer|exists:categories,id',
                'image_path' => 'required|string',
                'read_time' => 'required|integer',
                'saved_post_id' => 'nullable|integer|exists:saved_posts,id',

                // SEO Fields
                'meta_title' => 'nullable|string|max:80',
                'meta_description' => 'nullable|string|max:160',
                'focus_keyword' => 'nullable|string|max:100',
                'image_alt' => 'nullable|string|max:255',
                'og_title' => 'nullable|string|max:80',
                'og_description' => 'nullable|string|max:160',
                'og_image' => 'nullable|string',
                'twitter_title' => 'nullable|string|max:80',
                'twitter_description' => 'nullable|string|max:160',
                'twitter_image' => 'nullable|string',

                // Scheduling
                'use_scheduling' => 'boolean',
                'scheduled_at' => 'nullable|date|after:now',
                'use_expiration' => 'boolean',
                'expires_at' => 'nullable|date',
            ], [
                // Title validation messages
                'title.required' => 'Please enter a post title.',
                'title.max' => 'The post title cannot exceed 255 characters.',
                'title.unique' => 'A post with this title already exists. Please choose a different title.',

                // Excerpt validation messages
                'excerpt.required' => 'Please provide a short description for your post.',
                'excerpt.max' => 'The short description cannot exceed 510 characters.',

                // Body validation messages
                'body.required' => 'The post content cannot be empty.',

                // Category validation messages
                'category_id.required' => 'Please select a category for your post.',
                'category_id.integer' => 'Invalid category selection.',
                'category_id.exists' => 'The selected category does not exist.',

                // Image validation messages
                'image_path.required' => 'Please upload a featured image for your post.',
                'image_path.string' => 'Invalid image path.',

                // Read time validation messages
                'read_time.required' => 'Please specify the estimated reading time.',
                'read_time.integer' => 'Reading time must be a number.',

                // SEO Meta Title
                'meta_title.max' => 'The SEO title cannot exceed 80 characters for optimal search engine display.',

                // SEO Meta Description
                'meta_description.max' => 'The SEO description cannot exceed 160 characters for optimal search engine display.',

                // Focus Keyword
                'focus_keyword.max' => 'The focus keyword cannot exceed 100 characters.',

                // Image Alt Text
                'image_alt.max' => 'The image alt text cannot exceed 255 characters.',

                // Open Graph Title
                'og_title.max' => 'The Open Graph title cannot exceed 80 characters.',

                // Open Graph Description
                'og_description.max' => 'The Open Graph description cannot exceed 160 characters.',

                // Twitter Title
                'twitter_title.max' => 'The Twitter card title cannot exceed 80 characters.',

                // Twitter Description
                'twitter_description.max' => 'The Twitter card description cannot exceed 160 characters.',

                // Scheduling
                'scheduled_at.date' => 'Please enter a valid date and time for scheduling.',
                'scheduled_at.after' => 'The scheduled date must be in the future.',

                // Expiration
                'expires_at.date' => 'Please enter a valid expiration date.',
            ]);

            // START TRANSACTION - Ensure atomicity
            DB::beginTransaction();

            try {
                $post = Post::create([
                    'user_id' => auth()->id(),
                    'title' => $validated['title'],
                    'excerpt' => $validated['excerpt'],
                    'body' => $validated['body'],
                    'image_path' => $validated['image_path'],
                    'slug' => Str::slug($validated['title']),
                    'is_published' => true,
                    'category_id' => $validated['category_id'],
                    'read_time' => $validated['read_time'],
                    'change_user_id' => auth()->id(),
                    'changelog' => null,
                    'scheduled_at' => $request->use_scheduling && $request->scheduled_at ? $request->scheduled_at : null,
                    'expires_at' => $request->use_expiration && $request->expires_at ? $request->expires_at : null,

                    // SEO fields
                    'meta_title' => $validated['meta_title'],
                    'meta_description' => $validated['meta_description'],
                    'focus_keyword' => $validated['focus_keyword'],
                    'image_alt' => $validated['image_alt'],
                    'og_title' => $validated['og_title'],
                    'og_description' => $validated['og_description'],
                    'og_image' => $validated['og_image'],
                    'twitter_title' => $validated['twitter_title'],
                    'twitter_description' => $validated['twitter_description'],
                    'twitter_image' => $validated['twitter_image'],
                ]);

                // Delete the draft (inside transaction)
                if ($request->saved_post_id) {
                    SavedPost::where('id', $request->saved_post_id)
                        ->where('user_id', auth()->id())
                        ->delete();
                }

                // COMMIT - All DB operations succeeded
                DB::commit();

                // Send notification AFTER commit (outside transaction)
                // Notifications can fail without affecting data integrity
                try {
                    auth()->user()->notify(new PostNotification('SUCCESS', 'Post created', "/post/{$post->slug}"));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send post creation notification', [
                        'post_id' => $post->id,
                        'error' => $e->getMessage()
                    ]);
                }

                $message = 'Post published successfully!';
                if ($request->use_scheduling && $request->scheduled_at) {
                    $message = 'Post scheduled for ' . \Carbon\Carbon::parse($request->scheduled_at)->format('M d, Y H:i');
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.posts.index')
                ]);

            } catch (\Exception $e) {
                // ROLLBACK - Something failed in the transaction
                DB::rollBack();

                \Log::error('Post creation transaction failed', [
                    'user_id' => auth()->id(),
                    'title' => $validated['title'] ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the errors below and try again.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Post creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We encountered an error while creating your post. Please try again.'
            ], 500);
        }
    }

    public function autoSave(Request $request)
    {
        $validated = $request->validate([
            'saved_post_id' => 'nullable|integer|exists:saved_posts,id',
            'title' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:510',
            'body' => 'nullable|string',
            'image_path' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'read_time' => 'nullable|integer',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'use_scheduling' => 'boolean',
            'use_expiration' => 'boolean',

            // SEO fields
            'meta_title' => 'nullable|string|max:80',
            'meta_description' => 'nullable|string|max:160',
            'focus_keyword' => 'nullable|string|max:100',
            'image_alt' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:80',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|string',
            'twitter_title' => 'nullable|string|max:80',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_image' => 'nullable|string',
        ], [
            'title.max' => 'Title is too long (max 255 characters).',
            'excerpt.max' => 'Short description is too long (max 510 characters).',
            'meta_title.max' => 'SEO title is too long (max 80 characters).',
            'meta_description.max' => 'SEO description is too long (max 160 characters).',
            'focus_keyword.max' => 'Focus keyword is too long (max 100 characters).',
            'image_alt.max' => 'Image alt text is too long (max 255 characters).',
        ]);

        $draftData = [
            'title' => $validated['title'] ?: 'Untitled',
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'image_path' => $validated['image_path'],
            'category_id' => $validated['category_id'],
            'is_published' => false,
            'read_time' => $validated['read_time'],
            'scheduled_at' => $request->use_scheduling && $request->scheduled_at ? $request->scheduled_at : null,
            'expires_at' => $request->use_expiration && $request->expires_at ? $request->expires_at : null,

            // SEO fields
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'focus_keyword' => $validated['focus_keyword'],
            'image_alt' => $validated['image_alt'],
            'og_title' => $validated['og_title'],
            'og_description' => $validated['og_description'],
            'og_image' => $validated['og_image'],
            'twitter_title' => $validated['twitter_title'],
            'twitter_description' => $validated['twitter_description'],
            'twitter_image' => $validated['twitter_image'],
        ];

        // If saved_post_id exists, update that specific draft
        if ($request->saved_post_id) {
            $savedPost = SavedPost::find($request->saved_post_id);

            if ($savedPost && $savedPost->user_id === auth()->id()) {
                $savedPost->update($draftData);

                return response()->json([
                    'success' => true,
                    'message' => 'Auto-saved at ' . now()->format('H:i:s'),
                    'saved_post_id' => $savedPost->id
                ]);
            }
        }

        // Create new draft only if no saved_post_id was provided
        $savedPost = SavedPost::create(array_merge($draftData, [
            'user_id' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Draft saved at ' . now()->format('H:i:s'),
            'saved_post_id' => $savedPost->id,
            'update_url' => true
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:1024',
        ], [
            'image.required' => 'Please select an image to upload.',
            'image.image' => 'The file must be an image (jpg, jpeg, png, gif, svg, or webp).',
            'image.max' => 'The image size cannot exceed 1MB.',
        ]);

        $imageStorageService = app(\App\Services\ImageStorageService::class);
        $uploadedPath = $imageStorageService->storePostImage($request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully!',
            'path' => $uploadedPath
        ]);
    }

    public function uploadEditorImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:1024',
        ], [
            'image.required' => 'Please select an image to upload.',
            'image.image' => 'The file must be an image (jpg, jpeg, png, gif, svg, or webp).',
            'image.max' => 'The image size cannot exceed 1MB.',
        ]);

        $imageStorageService = app(\App\Services\ImageStorageService::class);
        $uploadedPath = $imageStorageService->storePostImage($request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded and inserted successfully!',
            'path' => $uploadedPath
        ]);
    }

    public function browseImages()
    {
        try {
            $imageAnalysisService = app(\App\Services\ImageAnalysisService::class);
            $directories = ['posts'];

            $analysisData = $imageAnalysisService->analyzeDirectories($directories);

            $images = collect($analysisData['files'])
                ->take(100)
                ->toArray();

            return response()->json([
                'success' => true,
                'images' => $images
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load images',
                'images' => []
            ]);
        }
    }

    public function browseEditorImages()
    {
        try {
            $imageAnalysisService = app(\App\Services\ImageAnalysisService::class);
            $directories = ['posts'];

            $analysisData = $imageAnalysisService->analyzeDirectories($directories);

            $images = collect($analysisData['files'])
                ->take(100)
                ->toArray();

            return response()->json([
                'success' => true,
                'images' => $images
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load images',
                'images' => []
            ]);
        }
    }

    public function autoSaveEdit(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:510',
            'body' => 'nullable|string',
            'image_path' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'read_time' => 'nullable|integer',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'use_scheduling' => 'boolean',
            'use_expiration' => 'boolean',
            'meta_title' => 'nullable|string|max:80',
            'meta_description' => 'nullable|string|max:160',
            'focus_keyword' => 'nullable|string|max:100',
            'image_alt' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:80',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|string',
            'twitter_title' => 'nullable|string|max:80',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_image' => 'nullable|string',
        ], [
            'title.max' => 'Title is too long (max 255 characters).',
            'excerpt.max' => 'Short description is too long (max 510 characters).',
            'meta_title.max' => 'SEO title is too long (max 80 characters).',
            'meta_description.max' => 'SEO description is too long (max 160 characters).',
        ]);

        // Store draft in history
        HistoryPost::updateOrCreate(
            [
                'post_id' => $id,
                'additional_info' => 2, // 2 = auto-save draft
            ],
            [
                'title' => $validated['title'] ?: $post->title,
                'excerpt' => $validated['excerpt'] ?: $post->excerpt,
                'body' => $validated['body'] ?: $post->body,
                'image_path' => $validated['image_path'] ?: $post->image_path,
                'category_id' => $validated['category_id'] ?: $post->category_id,
                'read_time' => $validated['read_time'] ?: $post->read_time,
                'scheduled_at' => $request->use_scheduling && $request->scheduled_at ? $request->scheduled_at : null,
                'expires_at' => $request->use_expiration && $request->expires_at ? $request->expires_at : null,
                'meta_title' => $validated['meta_title'],
                'meta_description' => $validated['meta_description'],
                'focus_keyword' => $validated['focus_keyword'],
                'image_alt' => $validated['image_alt'],
                'og_title' => $validated['og_title'],
                'og_description' => $validated['og_description'],
                'og_image' => $validated['og_image'],
                'twitter_title' => $validated['twitter_title'],
                'twitter_description' => $validated['twitter_description'],
                'twitter_image' => $validated['twitter_image'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Auto-saved at ' . now()->format('H:i:s')
        ]);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('post-edit')) {
            abort(403);
        }

        $post = Post::findOrFail($id);

        if ($post->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            abort(403);
        }

        $categories = Category::orderBy('name')->get();

        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('post-edit')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit posts.'
            ], 403);
        }

        $post = Post::findOrFail($id);

        if ($post->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to edit this post.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'title' => 'required|max:255|unique:posts,title,' . $id,
                'excerpt' => 'required|max:510',
                'body' => 'required',
                'category_id' => 'required|integer|exists:categories,id',
                'image_path' => 'required|string',
                'read_time' => 'required|integer',
                'is_published' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:80',
                'meta_description' => 'nullable|string|max:160',
                'focus_keyword' => 'nullable|string|max:100',
                'image_alt' => 'nullable|string|max:255',
                'og_title' => 'nullable|string|max:80',
                'og_description' => 'nullable|string|max:160',
                'og_image' => 'nullable|string',
                'twitter_title' => 'nullable|string|max:80',
                'twitter_description' => 'nullable|string|max:160',
                'twitter_image' => 'nullable|string',
                'use_scheduling' => 'boolean',
                'scheduled_at' => 'nullable|date|after:now',
                'use_expiration' => 'boolean',
                'expires_at' => 'nullable|date',
            ], [
                // Title validation messages
                'title.required' => 'Please enter a post title.',
                'title.max' => 'The post title cannot exceed 255 characters.',
                'title.unique' => 'A post with this title already exists. Please choose a different title.',

                // Excerpt validation messages
                'excerpt.required' => 'Please provide a short description for your post.',
                'excerpt.max' => 'The short description cannot exceed 510 characters.',

                // Body validation messages
                'body.required' => 'The post content cannot be empty.',

                // Category validation messages
                'category_id.required' => 'Please select a category for your post.',
                'category_id.integer' => 'Invalid category selection.',
                'category_id.exists' => 'The selected category does not exist.',

                // Image validation messages
                'image_path.required' => 'Please upload a featured image for your post.',

                // Read time validation messages
                'read_time.required' => 'Please specify the estimated reading time.',
                'read_time.integer' => 'Reading time must be a number.',

                // SEO validation messages
                'meta_title.max' => 'The SEO title cannot exceed 80 characters.',
                'meta_description.max' => 'The SEO description cannot exceed 160 characters.',
                'focus_keyword.max' => 'The focus keyword cannot exceed 100 characters.',
                'image_alt.max' => 'The image alt text cannot exceed 255 characters.',
                'og_title.max' => 'The Open Graph title cannot exceed 80 characters.',
                'og_description.max' => 'The Open Graph description cannot exceed 160 characters.',
                'twitter_title.max' => 'The Twitter card title cannot exceed 80 characters.',
                'twitter_description.max' => 'The Twitter card description cannot exceed 160 characters.',

                // Scheduling validation messages
                'scheduled_at.date' => 'Please enter a valid date and time for scheduling.',
                'scheduled_at.after' => 'The scheduled date must be in the future.',
                'expires_at.date' => 'Please enter a valid expiration date.',
            ]);

            // START TRANSACTION - Ensure atomicity
            DB::beginTransaction();

            try {
                // Build changelog by comparing old vs new
                $changelog = [];

                if ($post->title !== $validated['title']) $changelog[] = 'Title';
                if ($post->excerpt !== $validated['excerpt']) $changelog[] = 'Short description';
                if ($post->body !== $validated['body']) $changelog[] = 'Content';
                if ($post->category_id !== (int)$validated['category_id']) $changelog[] = 'Category';
                if ($post->image_path !== $validated['image_path']) $changelog[] = 'Image';
                if ($post->is_published !== (bool)($request->is_published ?? false)) $changelog[] = 'Visibility';

                // Snapshot the PREVIOUS version into HistoryPost (only if something changed)
                if (!empty($changelog)) {
                    HistoryPost::create([
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

                // Update the post
                $post->update([
                    'title' => $validated['title'],
                    'excerpt' => $validated['excerpt'],
                    'body' => $validated['body'],
                    'image_path' => $validated['image_path'],
                    'slug' => Str::slug($validated['title']),
                    'is_published' => $request->is_published ? true : false,
                    'category_id' => $validated['category_id'],
                    'read_time' => $validated['read_time'],
                    'change_user_id' => auth()->id(),
                    'changelog' => implode(', ', $changelog),
                    'additional_info' => 0,
                    'scheduled_at' => $request->use_scheduling && $request->scheduled_at ? $request->scheduled_at : null,
                    'expires_at' => $request->use_expiration && $request->expires_at ? $request->expires_at : null,
                    'meta_title' => $validated['meta_title'],
                    'meta_description' => $validated['meta_description'],
                    'focus_keyword' => $validated['focus_keyword'],
                    'image_alt' => $validated['image_alt'],
                    'og_title' => $validated['og_title'],
                    'og_description' => $validated['og_description'],
                    'og_image' => $validated['og_image'],
                    'twitter_title' => $validated['twitter_title'],
                    'twitter_description' => $validated['twitter_description'],
                    'twitter_image' => $validated['twitter_image'],
                ]);

                // Delete the auto-save draft if it exists
                HistoryPost::where('post_id', $post->id)
                    ->where('additional_info', 2)
                    ->delete();

                // COMMIT - All DB operations succeeded
                DB::commit();

                // Notify original author if edited by someone else (outside transaction)
                if (auth()->id() !== $post->user_id) {
                    try {
                        $post->user->notify(new PostNotification(
                            'INFO',
                            'The post has been edited by ' . auth()->user()->firstname . ' ' . auth()->user()->lastname . '.',
                            "/post/{$post->slug}"
                        ));
                    } catch (\Exception $e) {
                        \Log::warning('Failed to send post update notification', [
                            'post_id' => $post->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Post updated successfully!',
                    'redirect' => route('admin.posts.index')
                ]);

            } catch (\Exception $e) {
                // ROLLBACK - Something failed in the transaction
                DB::rollBack();

                \Log::error('Post update transaction failed', [
                    'post_id' => $id,
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the errors below and try again.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Post update failed', [
                'post_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We encountered an error while updating your post. Please try again.'
            ], 500);
        }
    }

    public function autoSaveCheck($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $autoSave = HistoryPost::where('post_id', $id)
            ->where('additional_info', 2)
            ->first();

        if ($autoSave) {
            return response()->json([
                'success' => true,
                'has_auto_save' => true,
                'auto_save' => [
                    'title' => $autoSave->title,
                    'slug' => $autoSave->slug,
                    'excerpt' => $autoSave->excerpt,
                    'body' => $autoSave->body,
                    'image_path' => $autoSave->image_path,
                    'category_id' => $autoSave->category_id,
                    'is_published' => $autoSave->is_published,
                    'read_time' => $autoSave->read_time,
                    'scheduled_at' => $autoSave->scheduled_at,
                    'expires_at' => $autoSave->expires_at,
                    'meta_title' => $autoSave->meta_title,
                    'meta_description' => $autoSave->meta_description,
                    'focus_keyword' => $autoSave->focus_keyword,
                    'image_alt' => $autoSave->image_alt,
                    'og_title' => $autoSave->og_title,
                    'og_description' => $autoSave->og_description,
                    'og_image' => $autoSave->og_image,
                    'twitter_title' => $autoSave->twitter_title,
                    'twitter_description' => $autoSave->twitter_description,
                    'twitter_image' => $autoSave->twitter_image,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'has_auto_save' => false
        ]);
    }

    public function cleanupImages(Request $request)
    {
        if (!auth()->user()->can('post-edit')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete images.'
            ], 403);
        }

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'string'
        ], [
            'images.required' => 'No images selected for cleanup.',
            'images.array' => 'Invalid image data format.',
        ]);

        $imageStorageService = app(\App\Services\ImageStorageService::class);
        $deletedCount = 0;

        foreach ($request->images as $imagePath) {
            try {
                $deleted = $imageStorageService->safeDelete($imagePath);
                if ($deleted) {
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete orphan image: ' . $imagePath, [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deletedCount} orphaned images",
            'deleted_count' => $deletedCount
        ]);
    }

    public function show()
    {
        // Placeholder - implementation not provided
    }

    public function analytics()
    {
        // Placeholder - implementation not provided
    }

    /**
     * Reject/delete the autosave draft
     */
    public function reject($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        HistoryPost::where('post_id', $id)
            ->where('additional_info', 2)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Auto-saved version discarded successfully.'
        ]);
    }
}
