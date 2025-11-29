<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperSecretKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('services.super_secret.key');

        $providedKey = $request->header('X-SUPER-SECRET-KEY');

        if (!$providedKey) {
            return response()->json([
                'message' => 'Missing X-SUPER-SECRET-KEY header.',
            ], 401);
        }

        if (!hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'Invalid X-SUPER-SECRET-KEY.',
            ], 403);
        }

        return $next($request);
    }
}
