<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Only add HSTS header if using HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy (CSP) - Allow Vite assets and HTTPS resources
        // style-src-elem and script-src-elem needed for Vite-generated assets
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "script-src-elem 'self' 'unsafe-inline'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' data: https://fonts.gstatic.com https://fonts.googleapis.com; " .
               "connect-src 'self';";
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
