<?php

use App\Http\Controllers\Pages\BookmarkController;
use App\Http\Controllers\Pages\CategoryController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\Pages\AboutController;
use App\Http\Controllers\Pages\ContactController;
use App\Http\Controllers\Pages\HomeController;
use App\Http\Controllers\Pages\PostController;
use App\Http\Controllers\Pages\SouthWestController;
use App\Http\Controllers\ProfileController;
use App\Models\HistoryPost;
use App\Models\SavedPost;
use App\Http\Controllers\Admin\{
    DashboardController as AdminDashboardController,
    AnalyticsController as AdminAnalyticsController,
    AnalyticsExportController as AdminAnalyticsExportController,
    ProfileController as AdminProfileController,
    ActivityLogController as AdminActivityLogController,
    SubscriberController as AdminSubscriberController,
    PostController as AdminPostController,
    SavedPostController as AdminSavedPostController,
    CategoryController as AdminCategoryController,
    CommentController as AdminCommentController,
    UserController as AdminUserController,
    RoleController as AdminRoleController,
    ImageController as AdminImageController,
    ContactMessageController as AdminContactMessageController,
    AdvertisementController as AdminAdvertisementController,
    PostHistoryController as AdminPostHistoryController
};
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/register', function () {
    return redirect()->route('login');
});

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::post('/load-more', [HomeController::class, 'loadMore'])->name('home.loadMore');
Route::get('/articles', [PostController::class, 'index'])->name('articles');
Route::get('/article/{slug}', [PostController::class, 'show'])->name('post.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/south-west-geo-data-integration', [SouthWestController::class, 'index'])->name('south.west');
Route::get('/about-us', [AboutController::class, 'index'])->name('about');
Route::get('/privacy-policy', function () {
    return view('pages.privacy-policy');
})->name('privacy.policy');
Route::get('/advertise', function () {
    return view('pages.advertise');
})->name('advertise');
Route::get('/terms-and-conditions', function () {
    return view('pages.terms-condition');
})->name('terms.conditions');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/subscribe-footer', [NewsletterController::class, 'subscribeFooter'])->name('newsletter.subscribe-footer');
Route::get('/search', function (\Illuminate\Http\Request $request) {
    $query = $request->input('q', '');
    $type = $request->input('type', 'all');

    if (strlen($query) < 2 && $type === 'all') {
        return response()->json(['results' => [], 'counts' => []]);
    }

    try {
        $results = [];
        $counts = ['all' => 0, 'posts' => 0, 'categories' => 0];

        // Search Posts with Scout Database Driver
        if ($type === 'all' || $type === 'posts') {
            $posts = Post::search($query)
                ->query(fn($builder) => $builder
                    ->where('is_published', true)
                    ->with([
                        'category:id,name,slug,backgroundColor,textColor',
                        'user:id,firstname,lastname'
                    ])
                    ->orderBy('created_at', 'desc')
                )
                ->take(50)
                ->get();

            $postResults = $posts->map(function ($post) {
                return [
                    'type' => 'post',
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt ?? '',
                    'image_path' => $post->image_path,
                    'read_time' => $post->read_time ?? 5,
                    'created_at_human' => $post->created_at->diffForHumans(),
                    'category' => $post->category ? [
                        'name' => $post->category->name,
                        'slug' => $post->category->slug,
                        'backgroundColor' => $post->category->backgroundColor,
                        'textColor' => $post->category->textColor,
                    ] : null,
                ];
            });

            $results = array_merge($results, $postResults->toArray());
            $counts['posts'] = $postResults->count();
        }

        // Search Categories with Scout Database Driver
        if ($type === 'all' || $type === 'categories') {
            $categories = Category::search($query)->get();

            $categoryResults = $categories->map(function ($category) {
                $postsCount = Post::where('category_id', $category->id)
                    ->where('is_published', true)
                    ->count();

                return [
                    'type' => 'category',
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'backgroundColor' => $category->backgroundColor,
                    'textColor' => $category->textColor,
                    'posts_count' => $postsCount,
                ];
            });

            $results = array_merge($results, $categoryResults->toArray());
            $counts['categories'] = $categoryResults->count();
        }

        $counts['all'] = count($results);

        return response()->json(['results' => $results, 'counts' => $counts]);

    } catch (\Exception $e) {
        return response()->json(['results' => [], 'counts' => [], 'error' => $e->getMessage()], 200);
    }
})->name('search');

Route::post('/track-ad-click', [PostController::class, 'trackAdClick'])->name('track-ad-click');

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');


require __DIR__ . '/auth.php';

// ============================================================================
// AUTHENTICATED ROUTES
// ============================================================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role'])->name('admin.')->prefix('admin')->group(function () {

    // Dashboard - Protected
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('index');
    // Dashboard AJAX endpoints
    Route::post('/refresh', [AdminDashboardController::class, 'refresh'])->name('refresh');
    Route::get('/analytics/data', [AdminDashboardController::class, 'getData'])->name('analytics.data');

    // Analytics - Protected
    Route::prefix('analytics')->name('analytics.')->middleware('permission:analytics-view')->group(function () {
        Route::get('/export/csv', [AdminAnalyticsExportController::class, 'exportCsv'])
            ->name('export.csv')
            ->middleware('permission:analytics-export');
        Route::get('/export/chart-data', [AdminAnalyticsExportController::class, 'exportChartData'])
            ->name('export.chart-data')
            ->middleware('permission:analytics-export');
    });

    // Profile - No permission needed (all authenticated users can access)
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

    // Activity Log - Protected
    Route::get('activity-log', [AdminActivityLogController::class, 'index'])
        ->name('activity.index')
        ->middleware('permission:activity-log-view');
    Route::post('activity-log/bulk-delete', [AdminActivityLogController::class, 'bulkDelete'])
        ->name('activity.bulk-delete')
        ->middleware('permission:activity-log-delete');
    Route::get('activity-log/{id}', [AdminActivityLogController::class, 'show'])
        ->name('activity.show')
        ->middleware('permission:activity-log-view');

    // Subscribers - Protected
    Route::prefix('subscribers')->name('subscribers.')->middleware('permission:subscriber-list')->group(function () {
        Route::get('/', [AdminSubscriberController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminSubscriberController::class, 'show'])
            ->name('show')
            ->middleware('permission:subscriber-view');
    });

    // Posts - Protected
    Route::prefix('posts')->name('posts.')->group(function () {
        // index post
        Route::get('/', [AdminPostController::class, 'index'])
            ->name('index')
            ->middleware('permission:post-list');
        Route::delete('/{id}', [AdminPostController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:post-delete');
        Route::post('/toggle-highlight', [AdminPostController::class, 'toggleHighlight'])->name('toggle-highlight');
        Route::post('/bulk-delete', [AdminPostController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-publish', [AdminPostController::class, 'bulkPublish'])->name('bulk-publish');
        Route::post('/bulk-unpublish', [AdminPostController::class, 'bulkUnpublish'])->name('bulk-unpublish');
        Route::post('/bulk-change-category', [AdminPostController::class, 'bulkChangeCategory'])->name('bulk-change-category');
        Route::post('/bulk-highlight', [AdminPostController::class, 'bulkHighlight'])->name('bulk-highlight');
        Route::post('/bulk-remove-highlight', [AdminPostController::class, 'bulkRemoveHighlight'])->name('bulk-remove-highlight');
        //create post
        Route::get('/create', [AdminPostController::class, 'create'])
            ->name('create')
            ->middleware('permission:post-create');
        Route::post('/', [AdminPostController::class, 'store'])
            ->name('store')
            ->middleware('permission:post-create');
        Route::post('/auto-save', [AdminPostController::class, 'autoSave'])->name('auto-save');
        Route::post('/upload-image', [AdminPostController::class, 'uploadImage'])->name('upload-image');
        Route::post('/upload-editor-image', [AdminPostController::class, 'uploadEditorImage'])->name('upload-editor-image');
        Route::get('/browse-images', [AdminPostController::class, 'browseImages'])->name('browse-images');
        Route::get('/browse-editor-images', [AdminPostController::class, 'browseEditorImages'])->name('browse-editor-images');

        //edit
        Route::get('/{id}/edit', [AdminPostController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:post-edit');
        Route::put('/{id}', [AdminPostController::class, 'update'])
            ->name('update')
            ->middleware('permission:post-edit');

        Route::get('/{slug}', [AdminPostController::class, 'show'])
            ->name('show')
            ->middleware('permission:post-list');

        Route::get('/{slug}/analytics', [AdminPostController::class, 'analytics'])
            ->name('analytics')
            ->middleware('permission:post-list');

        // Post API Routes - Protected
        Route::get('/{id}/show', function ($id) {
            if (!auth()->user()->can('post-list')) {
                abort(403);
            }
            return response()->json(Post::with('category')->findOrFail($id));
        })->name('show.json')->middleware('permission:post-list');

        Route::post('/{id}/auto-save', [AdminPostController::class, 'autoSaveEdit'])
            ->name('auto-save-edit')
            ->middleware('permission:post-edit');

        Route::post('/cleanup-images', [AdminPostController::class, 'cleanupImages'])
            ->name('cleanup-images')
            ->middleware('permission:post-edit');

        Route::delete('/posts/{id}/reject-autosave', [AdminPostController::class, 'reject'])->name('reject');

        Route::get('/{id}/auto-save-check', [AdminPostController::class, 'autoSaveCheck'])
            ->name('auto-save-check')
            ->middleware('permission:post-edit');

        Route::post('/calculate-read-time', function (Request $request) {
            $readingSpeed = 200;
            $words = str_word_count(strip_tags($request->get('body')));
            $readingTime = ceil($words / $readingSpeed);
            return response()->json($readingTime);
        })->name('read-time')->middleware('permission:post-create|post-edit');

        // Post History - Protected
        Route::get('/{id}/edit/history', [AdminPostHistoryController::class, 'index'])
            ->name('history.index')
            ->middleware('permission:post-list');

        Route::get('/{id}/edit/history/{history_id}/show', [AdminPostHistoryController::class, 'show'])
            ->name('history.show')
            ->middleware('permission:post-list');
        Route::post('/{id}/edit/history/{history_id}/revert', [AdminPostHistoryController::class, 'revert'])
            ->name('history.revert')
            ->middleware('permission:post-edit');

        Route::delete('/drafts/{id}', [AdminPostHistoryController::class, 'deleteDraft'])->name('drafts.delete');
    });

    // Saved Posts - Protected
    Route::prefix('posts-saved')->name('posts-saved.')->middleware('permission:post-list')->group(function () {
        Route::get('/', [AdminSavedPostController::class, 'index'])->name('index');

        Route::get('/{id}/edit', function ($id) {
            $saved = SavedPost::findOrFail($id);
            if ($saved->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
                abort(403);
            }
            return redirect('dashboard/posts/create?edit=' . $saved->id);
        })->name('edit')->middleware('permission:post-edit');

        Route::delete('/{id}', [AdminSavedPostController::class, 'destroy'])->name('destroy')->middleware('permission:post-delete');
    });

    // Categories - Protected
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'index'])
            ->name('index')
            ->middleware('permission:category-list');

        Route::get('/create', [AdminCategoryController::class, 'create'])
            ->name('create')
            ->middleware('permission:category-create');

        Route::post('/', [AdminCategoryController::class, 'store'])
            ->name('store')
            ->middleware('permission:category-create');

        Route::get('/{id}/edit', [AdminCategoryController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:category-edit');

        Route::put('/{id}', [AdminCategoryController::class, 'update'])
            ->name('update')
            ->middleware('permission:category-edit');

        Route::delete('/{id}', [AdminCategoryController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:category-delete');
    });

    // Comments - Protected
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [AdminCommentController::class, 'index'])
            ->name('index')
            ->middleware('permission:comment-list');

        Route::get('/{id}/edit', [AdminCommentController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:comment-edit');

        Route::put('/{id}', [AdminCommentController::class, 'update'])
            ->name('update')
            ->middleware('permission:comment-edit');

        Route::delete('/{id}', [AdminCommentController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:comment-delete');
    });

    // Users - Protected
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])
            ->name('index')
            ->middleware('permission:user-list');

        Route::get('/create', [AdminUserController::class, 'create'])
            ->name('create')
            ->middleware('permission:user-create');

        Route::post('/', [AdminUserController::class, 'store'])
            ->name('store')
            ->middleware('permission:user-create');

        Route::get('/{id}', [AdminUserController::class, 'show'])
            ->name('show')
            ->middleware('permission:user-list');

        Route::get('/{id}/edit', [AdminUserController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:user-edit');

        Route::put('/{id}', [AdminUserController::class, 'update'])
            ->name('update')
            ->middleware('permission:user-edit');

        Route::delete('/{id}', [AdminUserController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:user-delete');
    });

    // Roles - Protected
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [AdminRoleController::class, 'index'])
            ->name('index')
            ->middleware('permission:role-list');

        Route::get('/create', [AdminRoleController::class, 'create'])
            ->name('create')
            ->middleware('permission:role-create');

        Route::post('/', [AdminRoleController::class, 'store'])
            ->name('store')
            ->middleware('permission:role-create');

        Route::get('/{id}', [AdminRoleController::class, 'show'])
            ->name('show')
            ->middleware('permission:role-list');

        Route::get('/{id}/edit', [AdminRoleController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:role-edit');

        Route::put('/{id}', [AdminRoleController::class, 'update'])
            ->name('update')
            ->middleware('permission:role-edit');

        Route::delete('/{id}', [AdminRoleController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:role-delete');
    });

    Route::get('/load-more', [AdminImageController::class, 'loadMore'])->name('load-more');

    Route::prefix('images')->name('images.')->middleware('permission:image-list')->group(function () {
        Route::get('/', [AdminImageController::class, 'indexPage'])->name('index');

        Route::prefix('api')->name('api.')->group(function () {
            // Main image list with filters, sorting, pagination
            Route::get('/', [AdminImageController::class, 'index'])->name('index');

            // Get specific image info
            Route::get('/{directory}/{filename}', [AdminImageController::class, 'show'])->name('show');

            // Upload new image
            Route::post('/store', [AdminImageController::class, 'store'])
                ->middleware('permission:image-create')
                ->name('store');

            // Delete image
            Route::delete('/{directory}/{filename}', [AdminImageController::class, 'destroy'])
                ->middleware('permission:image-delete')
                ->name('destroy');

            // Get unused images
            Route::get('/unused', [AdminImageController::class, 'unused'])->name('unused');

            // Cleanup unused images
            Route::post('/cleanup-unused', [AdminImageController::class, 'cleanupUnused'])
                ->middleware('permission:image-delete')
                ->name('cleanup-unused');

            // Storage statistics
            Route::get('/stats', [AdminImageController::class, 'stats'])->name('stats');

            // Find duplicates
            Route::get('/duplicates', [AdminImageController::class, 'duplicates'])->name('duplicates');

            // Update user avatar
            Route::post('/avatar', [AdminImageController::class, 'updateAvatar'])
                ->name('update-avatar');
        });
    });

    // Contact Messages - Protected
    Route::prefix('contact-messages')->name('contact.')->middleware('permission:contact-list')->group(function () {
        Route::get('/', [AdminContactMessageController::class, 'index'])->name('index');

        Route::get('/{id}', [AdminContactMessageController::class, 'show'])
            ->name('show')
            ->middleware('permission:contact-view');

        Route::delete('/{id}', [AdminContactMessageController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:contact-delete');
    });

    // Advertisements - Protected
    Route::prefix('advertisements')->name('advertisements.')->group(function () {
        Route::get('/', [AdminAdvertisementController::class, 'index'])
            ->name('index')
            ->middleware('permission:advertisement-list');

        Route::get('/create', [AdminAdvertisementController::class, 'create'])
            ->name('create')
            ->middleware('permission:advertisement-create');

        Route::post('/', [AdminAdvertisementController::class, 'store'])
            ->name('store')
            ->middleware('permission:advertisement-create');

        Route::get('/{id}/edit', [AdminAdvertisementController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:advertisement-edit');

        Route::put('/{id}', [AdminAdvertisementController::class, 'update'])
            ->name('update')
            ->middleware('permission:advertisement-edit');

        Route::delete('/{id}', [AdminAdvertisementController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:advertisement-delete');

        // Track ad clicks
        Route::post('/{id}/click', function ($id) {
            $ad = \App\Models\Advertisement::findOrFail($id);
            $ad->recordClick();
            return response()->json(['success' => true]);
        })->name('click');
    });
});
