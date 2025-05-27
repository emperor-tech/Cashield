<?php

namespace App\Providers;

use App\Helpers\AuthHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

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
        
        // Replace the AuthManager with our custom version
        $this->app->singleton('auth', function ($app) {
            return new \App\Auth\CustomAuthManager($app);
        });
        
        // We'll use a different approach to fix the issue
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add a custom directive for auth checks
        Blade::if('authcheck', function () {
            return AuthHelper::check();
        });
        
        // Add a custom directive for guest checks
        Blade::if('guest', function () {
            return !AuthHelper::check();
        });
        
        // Add a custom directive for auth user
        Blade::directive('authuser', function () {
            return "<?php echo App\\Helpers\\AuthHelper::user() ? App\\Helpers\\AuthHelper::user()->name : ''; ?>";
        });
        
        // Add a custom directive for auth id
        Blade::directive('authid', function () {
            return "<?php echo App\\Helpers\\AuthHelper::id() ?? 'null'; ?>";
        });
        
        // Override Laravel's built-in auth directives to use our AuthHelper
        Blade::if('auth', function ($guard = null) {
            return AuthHelper::check();
        });
    }
}
