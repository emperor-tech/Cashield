<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthHelper
{
    /**
     * Get the currently authenticated user.
     *
     * @return object|null
     */
    public static function user()
    {
        return Auth::user();
    }
    
    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public static function guest()
    {
        return !Auth::check();
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool
     */
    public static function check()
    {
        return Auth::check();
    }
    
    /**
     * Get the ID of the currently authenticated user.
     *
     * @return int|null
     */
    public static function id()
    {
        return Auth::id();
    }
}