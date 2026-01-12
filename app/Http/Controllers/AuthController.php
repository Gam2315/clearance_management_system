<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function StudentForm()
    {
        return view('auth.student.login-student');
    }

    public function AdviserForm()
    {
        return view('auth.adviser.login-adviser');
    }

    public function AdminForm()
    {
        return view('auth.admin.login-admin');
    }

    public function DeanForm()
    {
        return view('auth.dean.login-dean');
    }

    public function EmployeeForm()
    {
        return view('auth.employee.login-employee');
    }

    /**
     * Universal login method for all user types
     * Supports both employee_id and student_number
     */
    public function loginAll(Request $request)
    {
        // Validate the login request
        $request->validate([
            'login' => 'required',  // This can be employee_id or student_number
            'password' => 'required',
        ]);

        // Find user by employee_id or student_number for universal login
        $user = User::where('employee_id', $request->login)
            ->orWhereHas('student', function ($query) use ($request) {
                $query->where('student_number', $request->login);
            })
            ->with('student')
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid Account! Please check your credentials.');
        }

        // Check if this specific account is locked due to failed attempts
        if ($user->isLocked()) {
            $remainingTime = $user->getRemainingLockoutTime();
            $minutes = ceil($remainingTime / 60);
            return back()->with('error', "This account is temporarily locked due to multiple failed login attempts. Please try again in {$minutes} minutes.");
        }

        // Check if account is active
        if ($user->status !== 'active') {
            return back()->with('error', 'Your account has been deactivated.');
        }

        // Attempt to log in using the found user's credentials
        $authCredentials = [];
        
        // Determine which field to use for authentication based on user type
        if ($user->employee_id && $user->employee_id === $request->login) {
            $authCredentials = ['employee_id' => $request->login, 'password' => $request->password];
        } elseif ($user->student && $user->student->student_number === $request->login) {
            $authCredentials = ['id' => $user->id, 'password' => $request->password];
        } else {
            // Fallback to name field if neither employee_id nor student_number matches
            $authCredentials = ['name' => $request->login, 'password' => $request->password];
        }
        
        if (Auth::attempt($authCredentials)) {
            // Clear failed attempts on successful login for this account
            $user->clearFailedAttempts();

            // Regenerate session ID for security
            $request->session()->regenerate();

            // Check if user needs to change password
            if ($user->password_changed_at === null || $user->force_password_change) {
                return redirect()->route('password.change.form');
            }

            // Redirect based on user role
            $redirectUrl = $this->getRedirectUrlByRole($user->role);
            return redirect()->intended($redirectUrl);
        }

        // Record failed attempt for this specific account
        $user->recordFailedAttempt();
        return back()->with('error', 'Incorrect Password.');
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrlByRole($role)
    {
        switch ($role) {
            case 'admin':
                return 'admin/dashboard';
            case 'student':
                return 'student/dashboard';
            case 'adviser':
                return 'adviser/dashboard';
            case 'dean':
                return 'dean/dashboard';
            case 'employee':
                return 'employee/dashboard';
            case 'officer':
                return 'officer/dashboard';
            default:
                return '/dashboard';
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function register(Request $request)
    {
        return view('auth.student.registration');
    }

    public function registered(Request $request)
    {
        return view('auth.adviser.registration');
    }

    public function storeStudent(Request $request)
    {
        // Student registration logic here
        return redirect()->route('auth.student.register')->with('success', 'Student registered successfully!');
    }

    public function storeAdviser(Request $request)
    {
        // Adviser registration logic here
        return redirect()->route('auth.adviser.register')->with('success', 'Adviser registered successfully!');
    }
}
