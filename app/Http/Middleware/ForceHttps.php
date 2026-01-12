<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force HTTPS in production
        if (config('security.headers.force_https') &&
            !$request->secure() &&
            app()->environment('production')) {

            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        // Add HTTPS security headers
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=' . config('security.headers.hsts_max_age', 31536000) . '; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
