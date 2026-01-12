<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clearance extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'academic_id',
        'department_id',
        'overall_status',
        'is_archived',
        'is_locked',
        'lock_reason',
        'locked_at',
        'locked_by',
        'can_unlock_roles',
        'semester',
        'previous_semester_completed',
    ];


    protected $table = 'clearances';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }   
    public function statuses()
    {
        return $this->hasMany(ClearanceStatus::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_id');
    }

    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'previous_semester_completed' => 'boolean',
        'can_unlock_roles' => 'array',
    ];

    /**
     * Check if clearance is accessible (not locked and student can proceed)
     */
    public function isAccessible()
    {
        if ($this->is_locked) {
            return false;
        }

        // Check if student has blocking violations
        if ($this->student->hasBlockingViolations()) {
            return false;
        }

        // Check if previous semester was completed (if required)
        // For new students using the system for the first time, assume previous semester was completed
        if ($this->previous_semester_completed === false) {
            return false;
        }

        // Check if student completed previous year clearance
        if (!$this->student->hasPreviousYearClearanceCompleted()) {
            return false;
        }

        return true;
    }

    /**
     * Lock the clearance
     */
    public function lock($reason, $userId = null)
    {
        $this->update([
            'is_locked' => true,
            'lock_reason' => $reason,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);
    }

    /**
     * Unlock the clearance
     */
    public function unlock()
    {
        $this->update([
            'is_locked' => false,
            'lock_reason' => null,
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    /**
     * Get required departments for this student's clearance
     */
    public function getRequiredDepartments()
    {
        return $this->student->getRequiredDepartments();
    }

    /**
     * Check if all required departments have cleared the student
     */
    public function isFullyCleared()
    {
        $requiredDepartments = $this->getRequiredDepartments();

        $clearedDepartments = $this->statuses()
            ->where('status', 'cleared')
            ->pluck('department_id')
            ->toArray();

        // Student is fully cleared only if ALL required departments have cleared them
        return count(array_intersect($requiredDepartments, $clearedDepartments)) === count($requiredDepartments);
    }

    /**
     * Get pending departments for this clearance
     */
    public function getPendingDepartments()
    {
        $requiredDepartments = $this->getRequiredDepartments();

        $clearedDepartments = $this->statuses()
            ->where('status', 'cleared')
            ->pluck('department_id')
            ->toArray();

        $pendingDepartmentIds = array_diff($requiredDepartments, $clearedDepartments);

        return \App\Models\Department::whereIn('id', $pendingDepartmentIds)
            ->pluck('department_name')
            ->toArray();
    }

    /**
     * Get clearance completion percentage
     */
    public function getCompletionPercentage()
    {
        $requiredDepartments = $this->getRequiredDepartments();
        $clearedDepartments = $this->statuses()
            ->where('status', 'cleared')
            ->count();

        return round(($clearedDepartments / count($requiredDepartments)) * 100, 2);
    }

    /**
     * Check if clearance should be locked based on previous semester
     */
    public function shouldBeLocked()
    {
        // If previous semester was not completed, lock the clearance
        if (!$this->previous_semester_completed) {
            return true;
        }

        // If student has blocking violations, lock the clearance
        if ($this->student && $this->student->hasBlockingViolations()) {
            return true;
        }

        return false;
    }

    /**
     * Auto-lock clearance if conditions are met
     */
    public function autoLock()
    {
        if ($this->shouldBeLocked() && !$this->is_locked) {
            $reason = 'Auto-locked: ';

            if (!$this->previous_semester_completed) {
                $reason .= 'Previous semester clearance not completed. ';
            }

            if ($this->student && $this->student->hasBlockingViolations()) {
                $reason .= 'Student has active violations.';
            }

            $this->lock(trim($reason), null);
        }
    }

    /**
     * Update overall status based on individual department statuses
     */
    public function updateOverallStatus()
    {
        $newStatus = $this->isFullyCleared() ? 'cleared' : 'pending';

        if ($this->overall_status !== $newStatus) {
            $this->update(['overall_status' => $newStatus]);
        }

        return $newStatus;
    }

}
