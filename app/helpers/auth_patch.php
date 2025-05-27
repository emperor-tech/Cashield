<?php

/**
 * This file contains a patch for the auth() helper function.
 * It will be loaded early in the bootstrap process.
 */

if (!function_exists('safe_auth')) {
    /**
     * Get the available auth instance.
     *
     * @param  string|null  $guard
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    function safe_auth($guard = null)
    {
        try {
            // Make sure guard is always a string or null
            if ($guard !== null) {
                if (is_object($guard)) {
                    \Illuminate\Support\Facades\Log::warning('Object passed to auth() instead of a string: ' . get_class($guard));
                    $guard = null; // Reset to default
                } elseif (is_array($guard)) {
                    \Illuminate\Support\Facades\Log::warning('Array passed to auth() instead of a string');
                    $guard = null; // Reset to default
                } elseif (!is_string($guard)) {
                    \Illuminate\Support\Facades\Log::warning('Non-string guard passed to auth(): ' . gettype($guard));
                    $guard = null; // Reset to default
                }
            }
            
            return app('auth')->guard($guard);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in auth() helper: ' . $e->getMessage());
            
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

// Create a global function to check if auth is working properly
if (!function_exists('is_auth_working')) {
    /**
     * Check if the auth system is working properly.
     *
     * @return bool
     */
    function is_auth_working()
    {
        try {
            // Don't store the result of safe_auth() in a variable
            // Just call the methods directly on the AuthHelper class
            \App\Helpers\AuthHelper::check();
            \App\Helpers\AuthHelper::guest();
            \App\Helpers\AuthHelper::user();
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Auth system is not working: ' . $e->getMessage());
            return false;
        }
    }
}