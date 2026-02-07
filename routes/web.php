<?php

use App\Http\Controllers\Pages\{
    BookmarkController,
    CategoryController,
    AboutController,
    ContactController,
    HomeController,
    PostController,
    SouthWestController
};
use App\Http\Controllers\{
    NewsletterController,
    ProfileController
};
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
    PageController as AdminPageController,
    CommentController as AdminCommentController,
    UserController as AdminUserController,
    RoleController as AdminRoleController,
    ImageController as AdminImageController,
    ContactMessageController as AdminContactMessageController,
    AdvertisementController as AdminAdvertisementController,
    PostHistoryController as AdminPostHistoryController
};
use App\Models\{Category, Post, SavedPost};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/register', fn() => redirect()->route('login'));

// Home & Articles
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::post('/load-more', [HomeController::class, 'loadMore'])->name('home.loadMore');
Route::get('/articles', [PostController::class, 'index'])->name('articles');
Route::get('/article/{slug}', [PostController::class, 'show'])->name('post.show');

// Categories
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

// Static Pages
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/south-west-geo-data-integration', function () {
    $page = \App\Models\Page::where('slug', 'south-west-geo-data-integration')->firstOrFail();
    return view('pages.south-west', compact('page'));
})->name('south.west');
Route::get('/about-us', [AboutController::class, 'index'])->name('about');
Route::get('/privacy-policy', function () {
    $page = \App\Models\Page::where('slug', 'privacy-policy')->firstOrFail();
    return view('pages.privacy-policy', compact('page'));
})->name('privacy.policy');
Route::get('/advertise', function () {
    $page = \App\Models\Page::where('slug', 'advertise')->firstOrFail();
    return view('pages.advertise', compact('page'));
})->name('advertise');
Route::get('/terms-and-conditions', function () {
    $page = \App\Models\Page::where('slug', 'terms-and-conditions')->firstOrFail();
    return view('pages.terms-condition', compact('page'));
})->name('terms.conditions');

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/subscribe-footer', [NewsletterController::class, 'subscribeFooter'])->name('newsletter.subscribe-footer');

// Search
Route::get('/search', function (Request $request) {
    $query = $request->input('q', '');
    $type = $request->input('type', 'all');

    if (strlen($query) < 2 && $type === 'all') {
        return response()->json(['results' => [], 'counts' => []]);
    }

    try {
        $results = [];
        $counts = ['all' => 0, 'posts' => 0, 'categories' => 0];

        // Search Posts
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

        // Search Categories
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

// Ad Tracking
Route::post('/track-ad-click', [PostController::class, 'trackAdClick'])->name('track-ad-click');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Bookmarks
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role'])->prefix('admin')->name('admin.')->group(function () {
    /*
   |--------------------------------------------------------------------------
   | Pages
   |--------------------------------------------------------------------------
   */
    Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [AdminPageController::class, 'index'])
            ->name('index')
            ->middleware('permission:page-list');
        Route::get('/{id}/edit', [AdminPageController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:page-edit');
        Route::put('/{id}', [AdminPageController::class, 'update'])
            ->name('update')
            ->middleware('permission:page-edit');
        // NEW: Page Image Handling Routes
        Route::post('/upload-editor-image', [AdminPageController::class, 'uploadEditorImage'])->name('upload-editor-image');
        Route::get('/browse-editor-images', [AdminPageController::class, 'browseEditorImages'])->name('browse-editor-images');
        Route::post('/cleanup-images', [AdminPageController::class, 'cleanupImages'])->name('cleanup-images');
    });

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('index');
    Route::post('/refresh', [AdminDashboardController::class, 'refresh'])->name('refresh');
    Route::get('/analytics/data', [AdminDashboardController::class, 'getData'])->name('analytics.data');

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */
    Route::prefix('analytics')->name('analytics.')->middleware('permission:analytics-view')->group(function () {
        Route::get('/export/csv', [AdminAnalyticsExportController::class, 'exportCsv'])
            ->name('export.csv')
            ->middleware('permission:analytics-export');
        Route::get('/export/chart-data', [AdminAnalyticsExportController::class, 'exportChartData'])
            ->name('export.chart-data')
            ->middleware('permission:analytics-export');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/upload-avatar', [AdminProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');
    Route::post('/profile/delete-avatar', [AdminProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::post('/profile/cleanup-avatars', [AdminProfileController::class, 'cleanupAvatars'])->name('profile.cleanup-avatars');

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    Route::prefix('activity-log')->name('activity.')->middleware('permission:activity-log-view')->group(function () {
        Route::get('/', [AdminActivityLogController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminActivityLogController::class, 'show'])->name('show');
        Route::post('/bulk-delete', [AdminActivityLogController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('permission:activity-log-delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Subscribers
    |--------------------------------------------------------------------------
    */
    Route::prefix('subscribers')->name('subscribers.')->middleware('permission:subscriber-list')->group(function () {
        Route::get('/', [AdminSubscriberController::class, 'index'])->name('index');
        Route::get('/data', [AdminSubscriberController::class, 'getData'])->name('data');
        Route::get('/stats', [AdminSubscriberController::class, 'getStats'])->name('stats');
        Route::get('/export', [AdminSubscriberController::class, 'export'])
            ->name('export')
            ->middleware('permission:subscriber-export');

        // Individual subscriber actions
        Route::get('/{id}', [AdminSubscriberController::class, 'show'])
            ->name('show')
            ->middleware('permission:subscriber-view');
        Route::delete('/{id}', [AdminSubscriberController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:subscriber-delete');
        Route::post('/{id}/resubscribe', [AdminSubscriberController::class, 'resubscribe'])->name('resubscribe');
        Route::post('/{id}/unsubscribe', [AdminSubscriberController::class, 'unsubscribe'])->name('unsubscribe');
        Route::post('/{id}/regenerate-token', [AdminSubscriberController::class, 'regenerateToken'])->name('regenerate-token');

        // Bulk actions
        Route::post('/bulk-delete', [AdminSubscriberController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('permission:subscriber-delete');
        Route::post('/bulk-resubscribe', [AdminSubscriberController::class, 'bulkResubscribe'])
            ->name('bulk-resubscribe')
            ->middleware('permission:subscriber-edit');
        Route::post('/bulk-unsubscribe', [AdminSubscriberController::class, 'bulkUnsubscribe'])
            ->name('bulk-unsubscribe')
            ->middleware('permission:subscriber-edit');
    });

    /*
    |--------------------------------------------------------------------------
    | Posts
    |--------------------------------------------------------------------------
    */
    Route::prefix('posts')->name('posts.')->group(function () {
        // List & Browse
        Route::get('/', [AdminPostController::class, 'index'])
            ->name('index')
            ->middleware('permission:post-list');
        Route::get('/{slug}', [AdminPostController::class, 'show'])
            ->name('show')
            ->middleware('permission:post-list');
        Route::get('/{slug}/analytics', [AdminPostController::class, 'analytics'])
            ->name('analytics')
            ->middleware('permission:post-list');
        Route::get('/{id}/show', function ($id) {
            if (!auth()->user()->can('post-list')) {
                abort(403);
            }
            return response()->json(Post::with('category')->findOrFail($id));
        })->name('show.json')->middleware('permission:post-list');

        // Create
        Route::get('/create', [AdminPostController::class, 'create'])
            ->name('create')
            ->middleware('permission:post-create');
        Route::post('/', [AdminPostController::class, 'store'])
            ->name('store')
            ->middleware('permission:post-create');
        Route::post('/auto-save', [AdminPostController::class, 'autoSave'])->name('auto-save');
        Route::post('/calculate-read-time', function (Request $request) {
            $readingSpeed = 200;
            $words = str_word_count(strip_tags($request->get('body')));
            $readingTime = ceil($words / $readingSpeed);
            return response()->json($readingTime);
        })->name('read-time')->middleware('permission:post-create|post-edit');

        // Edit
        Route::get('/{id}/edit', [AdminPostController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:post-edit');
        Route::put('/{id}', [AdminPostController::class, 'update'])
            ->name('update')
            ->middleware('permission:post-edit');
        Route::post('/{id}/auto-save', [AdminPostController::class, 'autoSaveEdit'])
            ->name('auto-save-edit')
            ->middleware('permission:post-edit');
        Route::get('/{id}/auto-save-check', [AdminPostController::class, 'autoSaveCheck'])
            ->name('auto-save-check')
            ->middleware('permission:post-edit');

        // Delete
        Route::delete('/{id}', [AdminPostController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:post-delete');
        Route::delete('/drafts/{id}', [AdminPostHistoryController::class, 'deleteDraft'])->name('drafts.delete');

        // Image Management
        Route::post('/upload-image', [AdminPostController::class, 'uploadImage'])->name('upload-image');
        Route::post('/upload-editor-image', [AdminPostController::class, 'uploadEditorImage'])->name('upload-editor-image');
        Route::get('/browse-images', [AdminPostController::class, 'browseImages'])->name('browse-images');
        Route::get('/browse-editor-images', [AdminPostController::class, 'browseEditorImages'])->name('browse-editor-images');
        Route::post('/cleanup-images', [AdminPostController::class, 'cleanupImages'])
            ->name('cleanup-images')
            ->middleware('permission:post-edit');

        // Bulk Actions
        Route::post('/toggle-highlight', [AdminPostController::class, 'toggleHighlight'])->name('toggle-highlight');
        Route::post('/bulk-delete', [AdminPostController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-publish', [AdminPostController::class, 'bulkPublish'])->name('bulk-publish');
        Route::post('/bulk-unpublish', [AdminPostController::class, 'bulkUnpublish'])->name('bulk-unpublish');
        Route::post('/bulk-change-category', [AdminPostController::class, 'bulkChangeCategory'])->name('bulk-change-category');
        Route::post('/bulk-highlight', [AdminPostController::class, 'bulkHighlight'])->name('bulk-highlight');
        Route::post('/bulk-remove-highlight', [AdminPostController::class, 'bulkRemoveHighlight'])->name('bulk-remove-highlight');

        // Post History
        Route::prefix('/{id}/edit/history')->name('history.')->middleware('permission:post-list')->group(function () {
            Route::get('/', [AdminPostHistoryController::class, 'index'])->name('index');
            Route::get('/{history_id}/show', [AdminPostHistoryController::class, 'show'])->name('show');
            Route::post('/{history_id}/revert', [AdminPostHistoryController::class, 'revert'])
                ->name('revert')
                ->middleware('permission:post-edit');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Saved Posts
    |--------------------------------------------------------------------------
    */
    Route::prefix('posts-saved')->name('posts-saved.')->middleware('permission:post-list')->group(function () {
        Route::get('/', [AdminSavedPostController::class, 'index'])->name('index');
        Route::get('/{id}/edit', function ($id) {
            $saved = SavedPost::findOrFail($id);
            if ($saved->user_id != auth()->id() && !auth()->user()->hasPermissionTo('post-super-list')) {
                abort(403);
            }
            return redirect('dashboard/posts/create?edit=' . $saved->id);
        })->name('edit')->middleware('permission:post-edit');
        Route::delete('/{id}', [AdminSavedPostController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:post-delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
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

        // Avatar Management
        Route::post('/upload-avatar', [AdminUserController::class, 'uploadAvatar'])
            ->name('upload-avatar')
            ->middleware('permission:user-create|user-edit');
        Route::post('/delete-avatar', [AdminUserController::class, 'deleteAvatar'])
            ->name('delete-avatar')
            ->middleware('permission:user-create|user-edit');
        Route::post('/cleanup-avatars', [AdminUserController::class, 'cleanupAvatars'])
            ->name('cleanup-avatars')
            ->middleware('permission:user-create|user-edit');

        // Bulk Actions
        Route::post('/bulk-delete', [AdminUserController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('permission:user-delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */
    Route::get('/load-more', [AdminImageController::class, 'loadMore'])->name('load-more');

    Route::prefix('images')->name('images.')->middleware('permission:image-list')->group(function () {
        Route::get('/', [AdminImageController::class, 'indexPage'])->name('index');

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/', [AdminImageController::class, 'index'])->name('index');
            Route::get('/{directory}/{filename}', [AdminImageController::class, 'show'])->name('show');
            Route::post('/store', [AdminImageController::class, 'store'])
                ->name('store')
                ->middleware('permission:image-create');
            Route::delete('/{directory}/{filename}', [AdminImageController::class, 'destroy'])
                ->name('destroy')
                ->middleware('permission:image-delete');
            Route::get('/unused', [AdminImageController::class, 'unused'])->name('unused');
            Route::post('/cleanup-unused', [AdminImageController::class, 'cleanupUnused'])
                ->name('cleanup-unused')
                ->middleware('permission:image-delete');
            Route::get('/stats', [AdminImageController::class, 'stats'])->name('stats');
            Route::get('/duplicates', [AdminImageController::class, 'duplicates'])->name('duplicates');
            Route::post('/avatar', [AdminImageController::class, 'updateAvatar'])->name('update-avatar');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Contact Messages
    |--------------------------------------------------------------------------
    */
    Route::prefix('contact-messages')->name('contact.')->middleware('permission:contact-list')->group(function () {
        Route::get('/', [AdminContactMessageController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminContactMessageController::class, 'show'])
            ->name('show')
            ->middleware('permission:contact-view');
        Route::post('/{id}/mark-replied', [AdminContactMessageController::class, 'markAsReplied'])
            ->name('mark-replied')
            ->middleware('permission:contact-view');
        Route::delete('/{id}', [AdminContactMessageController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:contact-delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Advertisements
    |--------------------------------------------------------------------------
    */
    Route::prefix('advertisements')->name('advertisements.')->middleware('permission:advertisement-list')->group(function () {
        Route::get('/', [AdminAdvertisementController::class, 'index'])->name('index');
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

        // Image Management
        Route::post('/upload-image', [AdminAdvertisementController::class, 'uploadImage'])
            ->name('upload-image')
            ->middleware('permission:advertisement-create|advertisement-edit');
        Route::post('/delete-image', [AdminAdvertisementController::class, 'deleteImage'])
            ->name('delete-image')
            ->middleware('permission:advertisement-create|advertisement-edit');

        // Status Toggle
        Route::post('/{id}/toggle-status', [AdminAdvertisementController::class, 'toggleStatus'])
            ->name('toggle-status')
            ->middleware('permission:advertisement-edit');
    });
});
