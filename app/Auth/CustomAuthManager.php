<?php

namespace App\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CustomAuthManager extends AuthManager
{
    /**
     * Get a guard instance by name.
     *
     * @param  mixed  $name
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard($name = null)
    {
        try {
            // Make sure name is a string or null
            if ($name !== null) {
                if ($name instanceof Request) {
                    Log::warning('Request object passed to guard() instead of a string');
                    $name = null; // Reset to default
                } elseif (is_array($name)) {
                    Log::warning('Array passed to guard() instead of a string: ' . json_encode($name));
                    $name = null; // Reset to default
                } elseif (is_object($name)) {
                    Log::warning('Object of class ' . get_class($name) . ' passed to guard() instead of a string');
                    $name = null; // Reset to default
                } elseif (!is_string($name)) {
                    Log::warning('Non-string guard name passed to guard(): ' . gettype($name));
                    $name = null; // Reset to default
                }
            }
            
            $name = $name ?: $this->getDefaultDriver();
            
            // Check if the guard exists in the guards array
            if (isset($this->guards[$name])) {
                return $this->guards[$name];
            }
            
            // If not, resolve it
            try {
                return $this->guards[$name] = $this->resolve($name);
            } catch (\Exception $e) {
                Log::error('Error resolving guard: ' . $e->getMessage());
                
                // Log the error but don't return a CustomGuard directly
                // Instead, create a dummy auth manager that won't throw errors
                return $this->guards[$name] = new class {
                    public function check() { return false; }
                    public function guest() { return true; }
                    public function user() { return null; }
                    public function id() { return null; }
                    public function validate() { return false; }
                    public function __call($method, $args) { return null; }
                };
            }
        } catch (\Exception $e) {
            Log::error('Critical error in guard method: ' . $e->getMessage());
            
            // Return a fallback guard
            return new CustomGuard(
                $this->createUserProvider(config('auth.providers.users.driver')),
                app('request')
            );
        }
    }
    
    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        try {
            return $this->guard()->{$method}(...$parameters);
        } catch (\Exception $e) {
            Log::error('Error in __call method: ' . $e->getMessage());
            
            // For user-related methods, return a sensible default
            if (in_array($method, ['check', 'guest', 'user', 'id', 'validate'])) {
                if ($method === 'check' || $method === 'validate') {
                    return false;
                } elseif ($method === 'guest') {
                    return true;
                } else {
                    return null;
                }
            }
            
            // For other methods, rethrow the exception
            throw $e;
        }
    }
    
    /**
     * Resolve the given guard.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function resolve($name)
    {
        try {
            return parent::resolve($name);
        } catch (\Exception $e) {
            Log::error('Error in resolve method: ' . $e->getMessage());
            
            // Return a fallback guard
            return new CustomGuard(
                $this->createUserProvider(config('auth.providers.users.driver')),
                app('request')
            );
        }
    }
}