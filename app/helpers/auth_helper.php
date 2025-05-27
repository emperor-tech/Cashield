<?php

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Log;

if (!function_exists('auth')) {
    /**
     * Get the available auth instance.
     *
     * @param  string|null  $guard
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    function auth($guard = null)
    {
        try {
            // Make sure guard is always a string or null
            if ($guard !== null && !is_string($guard)) {
                Log::warning('Non-string guard passed to auth(): ' . gettype($guard));
                $guard = null; // Reset to default
            }
            
            $authManager = app(AuthFactory::class);
            
            if (is_null($guard)) {
                return $authManager;
            }
            
            // Safely get the guard
            try {
                return $authManager->guard($guard);
            } catch (\Exception $e) {
                Log::warning('Error getting guard: ' . $e->getMessage());
                // Return the default guard as fallback
                return $authManager->guard(config('auth.defaults.guard', 'web'));
            }
        } catch (\Exception $e) {
            Log::error('Critical auth error: ' . $e->getMessage());
            // Log the error but don't return a CustomGuard directly
            // Instead, create a dummy auth manager that won't throw errors
            return new class {
                public function check() { return false; }
                public function guest() { return true; }
                public function user() { return null; }
                public function id() { return null; }
                public function validate() { return false; }
                public function __call($method, $args) { return null; }
            };
        }
    }
}