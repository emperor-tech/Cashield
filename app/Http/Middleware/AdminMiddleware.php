<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if the user is authenticated and has admin role
            if (Auth::check() && Auth::user()->role === 'admin') {
                return $next($request);
            }
        } catch (\Exception $e) {
            // If there's an error with Auth, try to get the user from session
            $userId = session('user_id');
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user && $user->role === 'admin') {
                    // Store the user in the request for later use
                    $request->attributes->set('user', $user);
                    return $next($request);
                }
            }
        }
        
        return redirect('/login');
    }
} 