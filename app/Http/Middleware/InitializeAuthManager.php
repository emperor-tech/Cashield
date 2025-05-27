<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InitializeAuthManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Intercept and fix any auth-related errors
        try {
            $guardName = config('auth.defaults.guard', 'custom');
            
            // First, ensure our auth manager is properly registered
            if (!app()->bound('auth')) {
                app()->singleton('auth', function ($app) {
                    return new \App\Auth\CustomAuthManager($app);
                });
            }
            
            // Then ensure our guard is properly registered
            if (!app()->bound('auth.driver.'.$guardName)) {
                app()->singleton('auth.driver.'.$guardName, function ($app) use ($guardName) {
                    $config = config('auth.guards.'.$guardName);
                    $provider = Auth::createUserProvider($config['provider'] ?? null);
                    // Make sure we're not passing the request directly to the guard
                    // Instead, get the request from the app container
                    return new \App\Auth\CustomGuard($provider, $app['request']);
                });
            }
            
            // Try to initialize the guard
            try {
                // Ensure we're passing a string, not a Request object
                if (is_string($guardName)) {
                    Auth::guard($guardName);
                } else {
                    Log::warning('Invalid guard name type: ' . gettype($guardName));
                    Auth::guard('custom'); // Use default guard name as fallback
                }
            } catch (\Exception $e) {
                Log::warning('Failed to initialize auth guard: ' . $e->getMessage());
            }
            
            // Sync session-based authentication
            if (Session::has('user_id')) {
                try {
                    if (!Auth::check()) {
                        Auth::loginUsingId(Session::get('user_id'));
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to login user from session in middleware: ' . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Critical error in auth middleware: ' . $e->getMessage());
        }
        
        // Get the response from the next middleware first
        $response = $next($request);

        // Don't try to call Auth::guard() after getting the response
        // This might be causing the issue

        // Always return the response
        return $response;
    }
}