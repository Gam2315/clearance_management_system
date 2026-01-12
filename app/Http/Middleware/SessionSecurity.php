<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for session hijacking
        if ($this->isSessionHijacked($request)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'security' => 'Session security violation detected. Please log in again.'
            ]);
        }

        // Regenerate session ID periodically for authenticated users
        if (auth()->check() && $this->shouldRegenerateSession($request)) {
            $request->session()->regenerate();
            session(['last_regeneration' => now()]);
        }

        // Store session fingerprint for security
        if (auth()->check() && !session('session_fingerprint')) {
            session([
                'session_fingerprint' => $this->generateSessionFingerprint($request),
                'last_regeneration' => now()
            ]);
        }

        return $next($request);
    }

    /**
     * Check if session might be hijacked
     */
    private function isSessionHijacked(Request $request): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $currentFingerprint = $this->generateSessionFingerprint($request);
        $storedFingerprint = session('session_fingerprint');

        // If fingerprints don't match, possible hijacking
        if ($storedFingerprint && $currentFingerprint !== $storedFingerprint) {
            Log::warning('Possible session hijacking detected', [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'stored_fingerprint' => $storedFingerprint,
                'current_fingerprint' => $currentFingerprint,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Generate session fingerprint
     */
    private function generateSessionFingerprint(Request $request): string
    {
        return hash('sha256',
            $request->ip() .
            $request->userAgent() .
            config('app.key')
        );
    }

    /**
     * Check if session should be regenerated
     */
    private function shouldRegenerateSession(Request $request): bool
    {
        $lastRegeneration = session('last_regeneration');

        if (!$lastRegeneration) {
            return true;
        }

        // Regenerate every 15 minutes
        return now()->diffInMinutes($lastRegeneration) >= 15;
    }
}
