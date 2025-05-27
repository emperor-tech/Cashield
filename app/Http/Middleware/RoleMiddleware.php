<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return redirect('/login');
            }

            $user = Auth::user();
            
            // Check if the user has one of the required roles
            foreach ($roles as $role) {
                if ($user->role === $role) {
                    return $next($request);
                }
            }
        } catch (\Exception $e) {
            // If there's an error with Auth, try to get the user from session
            $userId = session('user_id');
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    // Store the user in the request for later use
                    $request->attributes->set('user', $user);
                    
                    // Check if the user has one of the required roles
                    foreach ($roles as $role) {
                        if ($user->role === $role) {
                            return $next($request);
                        }
                    }
                }
            }
            
            return redirect('/login');
        }
        
        abort(403, 'Unauthorized');
    }
}