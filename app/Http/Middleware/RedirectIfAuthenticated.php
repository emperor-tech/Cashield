<?php

namespace App\Http\Middleware;

use App\Helpers\AuthHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        try {
            if (AuthHelper::check()) {
                return redirect('/dashboard');
            }
        } catch (\Exception $e) {
            Log::warning('Error in guest middleware: ' . $e->getMessage());
        }

        return $next($request);
    }
}