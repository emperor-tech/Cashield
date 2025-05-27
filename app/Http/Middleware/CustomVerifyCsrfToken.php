<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CustomVerifyCsrfToken extends BaseVerifier
{
    /**
     * Add the CSRF token to the response cookies.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response  $response
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    protected function addCookieToResponse($request, $response)
    {
        $config = config('session');

        Cookie::queue(
            'XSRF-TOKEN',
            $this->getTokenFromRequest($request),
            120, // 2 hours
            $config['path'] ?? '/',
            $config['domain'] ?? null,
            $config['secure'] ?? false,
            false, // httpOnly
            false, // raw
            $config['same_site'] ?? 'lax'
        );

        return $response;
    }
    
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
