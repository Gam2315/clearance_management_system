<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user needs to change password
            if ($user->password_changed_at === null || $user->force_password_change) {
                // Don't redirect if already on password change route or logout route
                if (!$request->routeIs('password.change.form') && 
                    !$request->routeIs('password.change.update') && 
                    !$request->routeIs('logout')) {
                    
                    // Store the intended URL
                    session(['url.intended' => $request->url()]);
                    
                    // Set session flag for modal
                    session(['show_password_change_modal' => true]);
                    
                    return redirect()->route('password.change.form');
                }
            }
        }

        return $next($request);
    }
}
