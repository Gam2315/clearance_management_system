<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validate API request format
        if (!$request->expectsJson()) {
            return response()->json([
                'error' => 'API requests must accept JSON responses'
            ], 400);
        }

        // Check for required headers
        if (!$request->hasHeader('Accept') || $request->header('Accept') !== 'application/json') {
            return response()->json([
                'error' => 'Invalid Accept header. Must be application/json'
            ], 400);
        }

        // Log API access for security monitoring
        Log::channel('security')->info('API request', [
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);

        // Check for suspicious patterns in request
        if ($this->containsSuspiciousContent($request)) {
            Log::channel('security')->warning('Suspicious API request blocked', [
                'endpoint' => $request->path(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'error' => 'Request blocked for security reasons'
            ], 403);
        }

        $response = $next($request);

        // Add security headers to API responses
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }

    /**
     * Check for suspicious content in request
     */
    private function containsSuspiciousContent(Request $request): bool
    {
        $suspiciousPatterns = [
            '/script\s*>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+set/i',
        ];

        $content = json_encode($request->all());

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}
