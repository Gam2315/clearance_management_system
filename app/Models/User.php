<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'firstname', 'middlename', 'lastname', 'employee_id', 'role', 'department_id'])
            ->useLogName('user')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        if ($eventName === 'created') {
            $activity->description = "A new user account was created for " . $this->lastname . ' ' . $this->firstname . ' ' . $this->middlename . ".";
        } elseif ($eventName === 'updated') {
          $activity->description = $this->lastname . ' ' . $this->firstname . ' ' . $this->middlename . ' profile was updated.';

        } elseif ($eventName === 'deleted') {
            $activity->description = "User " . $this->lastname . ' ' . $this->firstname . ' ' . $this->middlename . " was removed from the system.";
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_changed_at' => 'datetime',
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'force_password_change' => 'boolean',
    ];

    public function student()
    {
        return $this->hasOne(Student::class, 'users_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);

    }
    public function position()
    {
        return $this->belongsTo(Position::class,'position_id', 'id');
        
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    /**
     * Check if user account is locked
     */
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getRemainingLockoutTime()
    {
        if (!$this->isLocked()) {
            return 0;
        }

        return $this->locked_until->diffInSeconds(now());
    }

    /**
     * Check if user needs to change password
     */
    public function needsPasswordChange()
    {
        return $this->force_password_change || !$this->password_changed_at;
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts()
    {
        $this->increment('failed_login_attempts');

        // Lock account after 5 failed attempts for 30 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(30);
            $this->save();
        }
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedAttempts()
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Clear failed login attempts (alias for resetFailedAttempts)
     */
    public function clearFailedAttempts()
    {
        $this->resetFailedAttempts();
    }

    /**
     * Record a failed login attempt (alias for incrementFailedAttempts)
     */
    public function recordFailedAttempt()
    {
        $this->incrementFailedAttempts();
    }

    /**
     * Record successful login
     */
    public function recordLogin($ipAddress = null)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
    }
}
