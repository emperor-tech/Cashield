<?php

namespace App\Facades;

use Illuminate\Support\Facades\Auth as BaseAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * @method static \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard guard(string|null $name = null)
 * @method static \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard driver(string|null $name = null)
 * 
 * @see \Illuminate\Auth\AuthManager
 * @see \Illuminate\Contracts\Auth\Factory
 * @see \Illuminate\Contracts\Auth\Guard
 * @see \Illuminate\Contracts\Auth\StatefulGuard
 */
class Auth extends BaseAuth
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth';
    }
    
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        // Use our safe_auth function for guard method
        if ($method === 'guard') {
            return safe_auth($args[0] ?? null);
        }
        
        try {
            // For other methods, try to use safe_auth first
            if (in_array($method, ['check', 'guest', 'user', 'id', 'validate', 'attempt', 'login', 'loginUsingId', 'logout'])) {
                $auth = safe_auth();
                return $auth->{$method}(...$args);
            }
            
            // For other methods, call the original method
            return parent::__callStatic($method, $args);
        } catch (\Exception $e) {
            // If there's an error, log it
            Log::error('Auth error in ' . $method . '(): ' . $e->getMessage());
            
            // For user-related methods, return a sensible default
            if ($method === 'check' || $method === 'validate' || $method === 'attempt') {
                return false;
            } elseif ($method === 'guest') {
                return true;
            } elseif ($method === 'user' || $method === 'id') {
                return null;
            } elseif ($method === 'login' || $method === 'loginUsingId' || $method === 'logout') {
                return null;
            }
            
            // For other methods, rethrow the exception
            throw $e;
        }
    }
}