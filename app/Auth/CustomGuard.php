<?php

namespace App\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CustomGuard extends SessionGuard
{
    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider|null  $provider
     * @param  \Illuminate\Http\Request|null  $request
     * @return void
     */
    public function __construct($provider = null, $request = null)
    {
        // Handle null or invalid provider
        if ($provider === null || !($provider instanceof UserProvider)) {
            try {
                $provider = app('auth')->createUserProvider(config('auth.providers.users.driver'));
            } catch (\Exception $e) {
                Log::warning('Failed to create user provider: ' . $e->getMessage());
                try {
                    $provider = new \Illuminate\Auth\EloquentUserProvider(
                        app('hash'),
                        config('auth.providers.users.model', 'App\\Models\\User')
                    );
                } catch (\Exception $e2) {
                    Log::error('Failed to create fallback user provider: ' . $e2->getMessage());
                    // Create a minimal provider that won't throw errors
                    $provider = new class implements UserProvider {
                        public function retrieveById($identifier) { return null; }
                        public function retrieveByToken($identifier, $token) { return null; }
                        public function updateRememberToken(Authenticatable $user, $token) {}
                        public function retrieveByCredentials(array $credentials) { return null; }
                        public function validateCredentials(Authenticatable $user, array $credentials) { return false; }
                    };
                }
            }
        }
        
        // Handle null or invalid request
        if ($request === null || !($request instanceof Request)) {
            try {
                $request = app('request');
                if (!($request instanceof Request)) {
                    $request = Request::createFromGlobals();
                }
            } catch (\Exception $e) {
                Log::error('Failed to create request: ' . $e->getMessage());
                $request = Request::createFromGlobals();
            }
        }

        try {
            parent::__construct('custom', $provider, Session::driver(), $request);
        } catch (\Exception $e) {
            Log::error('Error initializing CustomGuard: ' . $e->getMessage());
            // We still need to set these properties even if parent constructor fails
            $this->name = 'custom';
            $this->provider = $provider;
            try {
                $this->session = Session::driver();
            } catch (\Exception $e2) {
                Log::error('Failed to get session driver: ' . $e2->getMessage());
                $this->session = null;
            }
            $this->request = $request;
        }
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        try {
            // Check if we already have a user set
            if ($this->user !== null) {
                return $this->user;
            }
            
            // First try the parent method
            try {
                $user = parent::user();
                if ($user) {
                    return $user;
                }
            } catch (\Exception $e) {
                Log::warning('Error in parent::user(): ' . $e->getMessage());
            }
            
            // Fall back to session-based auth if needed
            try {
                $userId = Session::get('user_id');
                
                if (!$userId) {
                    // Try to get user ID from request
                    try {
                        if ($this->request && $this->request->hasHeader('X-User-ID')) {
                            $userId = $this->request->header('X-User-ID');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error getting user ID from request: ' . $e->getMessage());
                    }
                    
                    if (!$userId) {
                        return null;
                    }
                }
                
                // Get the user from the provider
                if ($this->provider) {
                    try {
                        $user = $this->provider->retrieveById($userId);
                        
                        if ($user) {
                            $this->setUser($user);
                            return $user;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error retrieving user by ID: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error in session-based auth: ' . $e->getMessage());
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Critical error in CustomGuard::user(): ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        try {
            return $this->user() !== null;
        } catch (\Exception $e) {
            Log::warning('Error in check(): ' . $e->getMessage());
            return Session::has('user_id');
        }
    }
    
    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        try {
            return !$this->check();
        } catch (\Exception $e) {
            Log::warning('Error in guest(): ' . $e->getMessage());
            return !Session::has('user_id');
        }
    }
    
    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        try {
            if ($user = $this->user()) {
                return $user->getAuthIdentifier();
            }
        } catch (\Exception $e) {
            Log::warning('Error in id(): ' . $e->getMessage());
        }
        
        return Session::get('user_id');
    }
    
    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        try {
            return parent::validate($credentials);
        } catch (\Exception $e) {
            Log::warning('Error in validate(): ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        try {
            parent::setUser($user);
        } catch (\Exception $e) {
            Log::warning('Error in setUser(): ' . $e->getMessage());
            // Store user ID in session as fallback
            Session::put('user_id', $user->getAuthIdentifier());
        }
        
        return $this;
    }

    /**
     * Get the headers from the request.
     *
     * @return \Symfony\Component\HttpFoundation\HeaderBag
     */
    public function getHeaders()
    {
        return $this->request ? $this->request->headers : new \Symfony\Component\HttpFoundation\HeaderBag([]);
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    public function getBearerToken()
    {
        $header = $this->getHeaders()->get('Authorization', '');
        
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        
        return null;
    }

    /**
     * Handle missing method calls.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Don't forward to request object - this can cause issues
        try {
            return parent::__call($method, $parameters);
        } catch (\Exception $e) {
            Log::warning('Error in __call method: ' . $e->getMessage());
            
            // For common auth methods, return sensible defaults
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
     * Handle missing property access.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        // Don't forward to request object - this can cause issues
        try {
            return parent::__get($name);
        } catch (\Exception $e) {
            Log::warning('Error in __get method: ' . $e->getMessage());
            return null;
        }
    }
}