<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Redirect unauthenticated users to login
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Customer access required.',
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!auth()->user()->isCustomer()) {
            // Return 403 for authenticated non-customer users
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Customer access required.',
                ], 403);
            }
            abort(403, 'Unauthorized. Customer access required.');
        }

        return $next($request);
    }
}
