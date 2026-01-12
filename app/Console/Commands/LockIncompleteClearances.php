<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Clearance;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LockIncompleteClearances extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'clearance:lock-incomplete 
                            {--academic-year= : Specific academic year ID to lock}
                            {--dry-run : Show what would be locked without actually locking}
                            {--force : Force lock without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Lock clearances for students who did not complete their clearance in the specified academic year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Starting Clearance Locking Process...');
        $this->newLine();

        // Get academic year to process
        $academicYear = $this->getAcademicYear();
        if (!$academicYear) {
            $this->error('No academic year found to process.');
            return 1;
        }

        $this->info("Processing Academic Year: {$academicYear->academic_year} - {$academicYear->semester}");
        $this->newLine();

        // Find students with incomplete clearances
        $studentsToLock = $this->findStudentsWithIncompleteClearances($academicYear);

        if ($studentsToLock->isEmpty()) {
            $this->info('✅ No students found with incomplete clearances.');
            return 0;
        }

        $this->info("Found {$studentsToLock->count()} students with incomplete clearances:");
        $this->newLine();

        // Display students to be locked
        $this->displayStudentsToLock($studentsToLock);

        // Dry run check
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made.');
            return 0;
        }

        // Confirmation
        if (!$this->option('force') && !$this->confirm('Do you want to lock these clearances?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Lock the clearances
        $lockedCount = $this->lockClearances($studentsToLock, $academicYear);

        $this->info("✅ Successfully locked {$lockedCount} clearances.");
        
        // Send notifications
        $this->sendNotifications($studentsToLock, $academicYear);

        return 0;
    }

    /**
     * Get the academic year to process
     */
    private function getAcademicYear()
    {
        if ($academicYearId = $this->option('academic-year')) {
            return AcademicYear::find($academicYearId);
        }

        // Get the previous academic year (assuming current is the new one)
        return AcademicYear::where('status', '!=', 'active')
                          ->orderBy('created_at', 'desc')
                          ->first();
    }

    /**
     * Find students with incomplete clearances
     */
    private function findStudentsWithIncompleteClearances($academicYear)
    {
        return Student::whereHas('clearances', function($query) use ($academicYear) {
            $query->where('academic_id', $academicYear->id)
                  ->where('overall_status', '!=', 'cleared')
                  ->where('is_locked', false);
        })->with(['user', 'department', 'clearances' => function($query) use ($academicYear) {
            $query->where('academic_id', $academicYear->id);
        }])->get();
    }

    /**
     * Display students that will be locked
     */
    private function displayStudentsToLock($students)
    {
        $table = [];
        foreach ($students as $student) {
            $clearance = $student->clearances->first();
            $table[] = [
                $student->student_number,
                $student->user->firstname . ' ' . $student->user->lastname,
                $student->department->department_code ?? 'N/A',
                $clearance->overall_status ?? 'N/A'
            ];
        }

        $this->table(['Student Number', 'Name', 'Department', 'Status'], $table);
        $this->newLine();
    }

    /**
     * Lock the clearances
     */
    private function lockClearances($students, $academicYear)
    {
        $lockedCount = 0;
        $systemUser = User::where('role', 'admin')->first(); // System user for locking

        DB::transaction(function() use ($students, $academicYear, $systemUser, &$lockedCount) {
            foreach ($students as $student) {
                $clearance = $student->clearances->first();
                
                if ($clearance && !$clearance->is_locked) {
                    // Lock the clearance
                    $clearance->update([
                        'is_locked' => true,
                        'lock_reason' => "Incomplete clearance from Academic Year {$academicYear->academic_year} - {$academicYear->semester}. Student failed to complete clearance requirements before the deadline.",
                        'locked_at' => now(),
                        'locked_by' => $systemUser->id ?? null,
                        'can_unlock_roles' => ['admin', 'registrar']
                    ]);

                    // Update student record
                    $lockedYears = $student->locked_academic_years ? 
                                  json_decode($student->locked_academic_years, true) : [];
                    $lockedYears[] = [
                        'academic_year_id' => $academicYear->id,
                        'academic_year' => $academicYear->academic_year,
                        'semester' => $academicYear->semester,
                        'locked_at' => now()->toISOString()
                    ];

                    $student->update([
                        'has_locked_clearance' => true,
                        'locked_academic_years' => json_encode($lockedYears)
                    ]);

                    $lockedCount++;
                    $this->info("🔒 Locked: {$student->student_number} - {$student->user->firstname} {$student->user->lastname}");
                }
            }
        });

        return $lockedCount;
    }

    /**
     * Send notifications about locked clearances
     */
    private function sendNotifications($students, $academicYear)
    {
        $this->info('📧 Sending notifications...');
        
        // Here you would implement email notifications
        // For now, just log the action
        foreach ($students as $student) {
            $this->line("📧 Would notify: {$student->user->firstname} {$student->user->lastname} ({$student->student_number})");
        }
    }
}
