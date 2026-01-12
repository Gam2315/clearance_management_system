<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    /**
     * Show the password change form
     */
    public function showChangeForm()
    {
        $user = Auth::user();
        
        // Check if user needs to change password
        if ($user->password_changed_at !== null && !$user->force_password_change) {
            return redirect()->route($this->getRedirectRoute($user->role));
        }

        return view('auth.change-password', compact('user'));
    }

    /**
     * Handle password change request
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
        ], [
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'new_password.min' => 'Password must be at least 8 characters long.',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Check if new password is different from current
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors(['new_password' => 'New password must be different from current password.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
            'force_password_change' => false,
        ]);

        // Clear the modal session flag
        session()->forget('show_password_change_modal');

        // Get intended URL or default redirect
        $intendedUrl = session('url.intended');
        session()->forget('url.intended');

        if ($intendedUrl) {
            return redirect($intendedUrl)->with('success', 'Password changed successfully!');
        }

        return redirect()->route($this->getRedirectRoute($user->role))
                        ->with('success', 'Password changed successfully!');
    }

    /**
     * Get redirect route based on user role
     */
    private function getRedirectRoute($role)
    {
        switch ($role) {
            case 'admin':
                return 'admin.dashboard';
            case 'student':
                return 'student.dashboard';
            case 'adviser':
                return 'adviser.dashboard';
            case 'dean':
                return 'dean.dashboard';
            case 'employee':
                return 'employee.dashboard';
            case 'officer':
                return 'officer.dashboard';
            default:
                return 'dashboard';
        }
    }
}
