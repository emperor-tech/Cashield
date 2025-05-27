<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthGuardProxy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if auth is working properly
            if (!is_auth_working()) {
                Log::warning('Auth system is not working, using fallback');
                
                // Set a flag in the request to indicate we're using fallback auth
                $request->attributes->set('using_fallback_auth', true);
            }

            // Get the response from the next middleware first
            $nextResult = $next($request);
            
            // Ensure we have a valid Response object
            if (!($nextResult instanceof Response)) {
                Log::error('Invalid response type from next middleware: ' . (is_object($nextResult) ? get_class($nextResult) : gettype($nextResult)));
                
                // If it's a CustomGuard, it means something went wrong in the middleware chain
                if (is_object($nextResult) && get_class($nextResult) === 'App\\Auth\\CustomGuard') {
                    Log::error('CustomGuard object returned from middleware chain - this is a bug!');
                    // Create a new response
                    $response = response()->json(['error' => 'Internal server error'], 500);
                } else {
                    // For other types, try to convert to a response
                    try {
                        if (is_string($nextResult)) {
                            $response = response($nextResult);
                        } elseif (is_array($nextResult)) {
                            $response = response()->json($nextResult);
                        } else {
                            $response = response()->json(['error' => 'Invalid response type'], 500);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to convert next middleware result to response: ' . $e->getMessage());
                        $response = response()->json(['error' => 'Internal server error'], 500);
                    }
                }
            } else {
                $response = $nextResult;
            }
            
            // Don't try to call Auth::check() after getting the response
            // This might be causing the issue
            
            // Always return the response (which we've already validated)
            return $response;
            
        } catch (\Exception $e) {
            // Handle any critical errors
            Log::error('Critical error in AuthGuardProxy: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}