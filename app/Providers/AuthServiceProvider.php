<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthServiceProvider as LaravelAuthServiceProvider;

class AuthServiceProvider extends LaravelAuthServiceProvider
{
    protected $policies = [];

    public function register(): void
    {
        parent::register();

        // Register our custom auth manager first
        $this->app->singleton('auth', function ($app) {
            // This ensures we're using our custom auth manager
            return new \App\Auth\CustomAuthManager($app);
        });
    }

    public function boot(): void
    {
        // Register our custom guard driver
        Auth::extend('custom', function ($app, $name, array $config) {
            try {
                $provider = Auth::createUserProvider($config['provider'] ?? 'users');
                return new \App\Auth\CustomGuard($provider, $app['request']);
            } catch (\Exception $e) {
                Log::error('Failed to create custom guard: ' . $e->getMessage());
                // Create provider for the basic guard
                $provider = new \Illuminate\Auth\EloquentUserProvider(
                    app('hash'),
                    config('auth.providers.users.model', 'App\\Models\\User')
                );
                return new \App\Auth\CustomGuard($provider, $app['request']);
            }
        });

        // Register our custom session guard resolver
        Auth::viaRequest('session', function ($request) {
            try {
                // Store the request in the app container
                app()->instance('request', $request);
                
                $provider = Auth::createUserProvider(config('auth.providers.users.driver'));
                return new \App\Auth\CustomGuard($provider, app('request'));
            } catch (\Exception $e) {
                Log::error('Failed to create session guard: ' . $e->getMessage());
                // Create provider for the session guard
                $provider = new \Illuminate\Auth\EloquentUserProvider(
                    app('hash'),
                    config('auth.providers.users.model', 'App\\Models\\User')
                );
                return new \App\Auth\CustomGuard($provider, app('request'));
            }
        });

        // Sync session-based authentication with Laravel Auth
        if (Session::has('user_id')) {
            try {
                if (!Auth::check()) {
                    Auth::loginUsingId(Session::get('user_id'));
                }
            } catch (\Exception $e) {
                Log::warning('Failed to login user from session: ' . $e->getMessage());
            }
        }

        // Initialize the default guard
        try {
            $defaultGuard = config('auth.defaults.guard', 'custom');
            Auth::guard($defaultGuard);
        } catch (\Exception $e) {
            Log::warning('Failed to initialize auth guard: ' . $e->getMessage());
        }

        // Don't modify the middleware stack here
        // This was causing conflicts with the middleware defined in Kernel.php
    }
}