<?php

namespace App\Providers;

use App\Helpers\ActivityLogger;
use App\Models\HistoryPost;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\User;
use App\Observers\HistoryPostObserver;
use App\Observers\PermissionObserver;
use App\Observers\PostObserver;
use App\Observers\RoleObserver;
use App\Observers\SavedPostObserver;
use App\Observers\UserObserver;
use App\Repositories\ImageRepository;
use App\Services\ImageAnalysisService;
use App\Services\ImageFilterService;
use App\Services\ImageStorageService;
use App\Services\ImageUsageService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(ImageRepository::class);

        // Register services
        $this->app->singleton(ImageUsageService::class);

        $this->app->singleton(ImageStorageService::class, function ($app) {
            return new ImageStorageService(
                $app->make(ImageRepository::class),
                $app->make(ImageUsageService::class)
            );
        });

        $this->app->singleton(ImageAnalysisService::class, function ($app) {
            return new ImageAnalysisService(
                $app->make(ImageRepository::class),
                $app->make(ImageUsageService::class)
            );
        });

        $this->app->singleton(ImageFilterService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Post::observe(PostObserver::class);
        SavedPost::observe(SavedPostObserver::class);
        HistoryPost::observe(HistoryPostObserver::class);
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);

        // Log successful login
        Event::listen(Login::class, function (Login $event) {
            ActivityLogger::logLogin($event->user);
        });

        // Log successful logout
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLogger::logLogout($event->user);
            }
        });

        // Log failed login attempts
        Event::listen(Failed::class, function (Failed $event) {
            ActivityLogger::logFailedLogin(
                $event->credentials['email'] ?? 'unknown',
                request()->ip()
            );
        });
    }
}
