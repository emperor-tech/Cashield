<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('files', function (Application $app) {
            return new \Illuminate\Filesystem\Filesystem;
        });
        
        // Standard Laravel Auth is used, no custom auth manager needed
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Tailwind pagination views
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        // Use Laravel's built-in auth directives
        Blade::if('auth', function ($guard = null) {
            return Auth::check();
        });

        Blade::if('guest', function ($guard = null) {
            return !Auth::check();
        });

        Blade::directive('authuser', function () {
            return "<?php echo Auth::user() ? Auth::user()->name : ''; ?>";
        });

        Blade::directive('authid', function () {
            return "<?php echo Auth::id() ?? 'null'; ?>";
        });
    }
}
