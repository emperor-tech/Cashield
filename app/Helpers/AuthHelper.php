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
        try {
            // Use our custom guard which handles both Laravel Auth and session-based auth
            return Auth::guard('custom')->user();
        } catch (\Exception $e) {
            // Fall back to session-based auth if there's an error
            $userId = Session::get('user_id');
            
            if (!$userId) {
                return null;
            }
            
            return DB::table('users')->where('id', $userId)->first();
        }
    }
    
    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public static function guest()
    {
        return !static::check();
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool
     */
    public static function check()
    {
        try {
            // Use our custom guard which handles both Laravel Auth and session-based auth
            return Auth::guard('custom')->check();
        } catch (\Exception $e) {
            // Fall back to session-based auth if there's an error
            return Session::has('user_id');
        }
    }
    
    /**
     * Get the ID of the currently authenticated user.
     *
     * @return int|null
     */
    public static function id()
    {
        try {
            // Use our custom guard which handles both Laravel Auth and session-based auth
            return Auth::guard('custom')->id();
        } catch (\Exception $e) {
            // Fall back to session-based auth if there's an error
            return Session::get('user_id');
        }
    }
}