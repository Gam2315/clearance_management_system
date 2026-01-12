<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;

class Student extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table = 'students';
    protected $fillable = [
         'users_id',
        'student_number',
         'course_id',
         'year',
         'department_id',
         'academic_id',
         'nfc_uid',
         'is_archived',
         'has_violations',
         'is_graduated',
         'clearance_history',
         'first_year_clearance_completed',
         'is_uniwide',
         'has_locked_clearance',
         'locked_academic_years',
    ];

    protected $casts = [
        'clearance_history' => 'array',
        'is_archived' => 'boolean',
        'is_uniwide' => 'boolean',
        'has_locked_clearance' => 'boolean',
        'locked_academic_years' => 'array',
        'has_violations' => 'boolean',
        'is_graduated' => 'boolean',
        'first_year_clearance_completed' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'users_id',
                'student_number',
                'course_id',
                'year',
                'department_id',
                'has_completed_requirements',
                'academic_id',
                'nfc_uid',
                'is_archived',
            ])
            ->useLogName('student')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $lastname = $this->user->lastname ?? '';
        $firstname = $this->user->firstname ?? '';
        $middlename = $this->user->middlename ?? '';

        if ($eventName === 'created') {
            $activity->description = "A new student account was created for {$lastname} {$firstname} {$middlename}.";
        } elseif ($eventName === 'updated') {
            $activity->description = "Student {$lastname} {$firstname} {$middlename} profile was updated.";
        } elseif ($eventName === 'deleted') {
            $activity->description = "Student {$lastname} {$firstname} {$middlename} was removed from the system.";
        }
    }
    public function user()
    {
        //return $this->belongsTo(User::class, 'id', 'users_id');
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function AY()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_id', 'id');
    }

    public function clearances()
    {
        return $this->hasMany(Clearance::class, 'student_id', 'id');
    }

    /**
     * Check if student can proceed with clearance
     */
    public function canProceedWithClearance()
    {
        // Check if student is graduated
        if ($this->is_graduated) {
            return false;
        }

        // Check if student has blocking violations
        if ($this->hasBlockingViolations()) {
            return false;
        }

        // Check if previous year clearance was completed
        if (!$this->hasPreviousYearClearanceCompleted()) {
            return false;
        }

        return true;
    }

    /**
     * Check if student has blocking violations
     */
    public function hasBlockingViolations()
    {
        return $this->has_violations;
    }

    /**
     * Check if student completed previous year clearance
     */
    public function hasPreviousYearClearanceCompleted()
    {
        // For first year students, always return true
        if ($this->year === '1st' || $this->year === 'First Year' || $this->year === '1') {
            return true;
        }

        // For existing students using the system for the first time:
        // If the field is null, assume they completed previous clearances
        // This handles the case where students are migrating to the new system
        if ($this->first_year_clearance_completed === null) {
            return true;
        }

        // Check if first year clearance was completed
        return $this->first_year_clearance_completed;
    }

    /**
     * Get required departments for this student based on their course
     */
    public function getRequiredDepartments()
    {
        // Base service departments that ALL students need clearance from
        $baseDepartments = [5, 8, 10, 6, 9, 13, 11, 14, 7]; // BAO, Clinic, Library, OSA, Research, CF, Registrar, Boutique, Guidance

        // Course-specific departments
        $courseSpecificDepartments = [];
        $studentCourse = $this->courses ? $this->courses->course_code : null;
        $studentDept = $this->department_id;

        // FOODLAB (ID: 12) - Only for SBAHM students
        if ($studentDept == 4) { // SBAHM
            $courseSpecificDepartments[] = 17;
        }

        // Computer (ID: 29) - Only for SITE IT/Computer Engineering students
        if ($studentDept == 1 && $studentCourse && in_array($studentCourse, ['BSIT', 'BSCpE'])) {
            $courseSpecificDepartments[] = 15;
        }

        // Engineering (ID: 28) - Only for SITE engineering students
        if ($studentDept == 1 && $studentCourse && in_array($studentCourse, ['BSCE', 'BSEnSE', 'BSCpE'])) {
            $courseSpecificDepartments[] = 16;
        }

        // Science Lab (ID: 24) - Only for SASTE science students
        if ($studentDept == 2 && $studentCourse && in_array($studentCourse, ['BSBio', 'BSBio-MicroBiology', 'BSPsych'])) {
            $courseSpecificDepartments[] = 12;
        }

        // UNIWIDE students don't need additional department requirements
        // They use the PSG - SITE: ADVISER section instead
        // if ($this->is_uniwide) {
        //     $courseSpecificDepartments[] = 17;
        // }

        // Student's own academic department + base departments + course-specific departments
        $requiredDepartments = array_merge([$this->department_id], $baseDepartments, $courseSpecificDepartments);

        return array_unique($requiredDepartments);
    }

    /**
     * Update clearance history
     */
    public function updateClearanceHistory($academicId, $semester, $completed)
    {
        $history = $this->clearance_history ?? [];

        $key = $academicId . '_' . $semester;
        $history[$key] = [
            'academic_id' => $academicId,
            'semester' => $semester,
            'completed' => $completed,
            'completed_at' => now()->toISOString()
        ];

        $this->update(['clearance_history' => $history]);

        // Update first year clearance completion flag if applicable
        if ($this->year === '1st' || $this->year === 'First Year' || $this->year === '1') {
            $this->update(['first_year_clearance_completed' => $completed]);
        }
    }
}
