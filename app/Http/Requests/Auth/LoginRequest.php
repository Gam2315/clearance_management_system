<?php

namespace App\Http\Requests\Auth;

use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
             'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->login; // store it in a variable

        $user = User::where('employee_id', $login)
            ->orWhereHas('student', function ($query) use ($login) {
                $query->where('student_number', $login);
            })
            ->with('student') // Optional: eager load the student
            ->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            throw ValidationException::withMessages([
                'login' => 'Account is temporarily locked due to multiple failed login attempts. Please try again later.',
            ]);
        }

        // Check if account is inactive
        if ($user->status === 'inactive') {
            throw ValidationException::withMessages([
                'login' => 'Your account is inactive. Please contact the administrator.',
            ]);
        }

        // Verify password
        if (!Hash::check($this->password, $user->password)) {
            $user->incrementFailedAttempts();
            RateLimiter::hit($this->throttleKey());

            // Create detailed error message with security info
            $attemptsLeft = 5 - $user->failed_login_attempts;
            $errorMessage = trans('auth.failed');

            if ($user->failed_login_attempts >= 3) {
                $errorMessage .= " Warning: " . $attemptsLeft . " attempts remaining before account lockout.";
            }

            if ($user->failed_login_attempts >= 5) {
                $errorMessage = "Account locked for 30 minutes due to multiple failed attempts. Security measure activated.";
            }

            throw ValidationException::withMessages([
                'login' => $errorMessage,
            ]);
        }

        // Successful login
        $user->recordLogin($this->ip());

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }

    /**
     * Check if this is a new device or location
     */
    private function isNewDeviceOrLocation($user): bool
    {
        // Simple check - if last login IP is different or no previous login
        return !$user->last_login_ip || $user->last_login_ip !== $this->ip();
    }
}
