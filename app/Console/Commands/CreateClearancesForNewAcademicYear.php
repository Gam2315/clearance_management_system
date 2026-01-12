<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\AcademicYear;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class CreateClearancesForNewAcademicYear extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'clearance:create-for-new-year 
                            {--academic-year= : Specific academic year ID to create clearances for}
                            {--dry-run : Show what would be created without actually creating}
                            {--force : Force creation even if clearances already exist}';

    /**
     * The console command description.
     */
    protected $description = 'Create clearances for students in the new active academic year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎓 Creating clearances for new academic year...');

        // Get the target academic year
        $academicYearId = $this->option('academic-year');
        if ($academicYearId) {
            $academicYear = AcademicYear::find($academicYearId);
            if (!$academicYear) {
                $this->error("Academic year with ID {$academicYearId} not found.");
                return 1;
            }
        } else {
            // Get the active academic year
            $academicYear = AcademicYear::where('status', 'active')->first();
            if (!$academicYear) {
                $this->error('No active academic year found.');
                return 1;
            }
        }

        $this->info("Target Academic Year: {$academicYear->academic_year} - {$academicYear->semester}");

        // Get all non-archived students
        $students = Student::where('is_archived', false)->get();
        
        if ($students->isEmpty()) {
            $this->warn('No active students found.');
            return 0;
        }

        $this->info("Found {$students->count()} active students");

        // Check for existing clearances
        $existingClearances = Clearance::where('academic_id', $academicYear->id)->count();
        
        if ($existingClearances > 0 && !$this->option('force')) {
            $this->warn("Found {$existingClearances} existing clearances for this academic year.");
            $this->warn('Use --force to create clearances anyway, or --dry-run to see what would be created.');
            return 0;
        }

        // Get departments that require clearance
        $departments = Department::whereIn('id', [1, 2, 3, 4])->get(); // Academic departments
        $serviceDepartments = Department::whereIn('department_code', [
            'BAO', 'CLINIC', 'FOODLAB', 'LIBRARY', 'OSA', 'RESEARCH'
        ])->get();
        $spupDepartments = Department::whereIn('department_code', ['SPUP'])->get(); // SPUP only

        $allDepartments = $departments->merge($serviceDepartments)->merge($spupDepartments);

        if ($this->option('dry-run')) {
            $this->info("\n🔍 DRY RUN - No changes will be made");
            $this->showDryRunResults($students, $academicYear, $allDepartments);
            return 0;
        }

        // Update students to the new academic year first
        $this->info("\n📝 Updating students to new academic year...");
        $studentsToUpdate = Student::where('is_archived', false)
            ->where('academic_id', '!=', $academicYear->id)
            ->count();

        $studentsUpdatedCount = 0;
        if ($studentsToUpdate > 0) {
            $studentsUpdatedCount = Student::where('is_archived', false)
                ->where('academic_id', '!=', $academicYear->id)
                ->update(['academic_id' => $academicYear->id]);
            $this->info("Updated {$studentsUpdatedCount} students to academic year {$academicYear->id}");
        } else {
            $this->info("All students are already assigned to academic year {$academicYear->id}");
        }

        // Create clearances
        $created = 0;
        $updated = 0;
        $errors = 0;

        $this->info("\n📝 Creating clearances...");
        $progressBar = $this->output->createProgressBar($students->count());

        DB::transaction(function() use ($students, $academicYear, $allDepartments, &$created, &$updated, &$errors, $progressBar) {
            foreach ($students as $student) {
                try {
                    // Check if clearance already exists
                    $existingClearance = Clearance::where('student_id', $student->id)
                        ->where('academic_id', $academicYear->id)
                        ->first();

                    if ($existingClearance) {
                        if ($this->option('force')) {
                            // Update existing clearance
                            $existingClearance->update([
                                'overall_status' => 'pending',
                                'is_archived' => false,
                                'previous_semester_completed' => $this->checkPreviousSemesterCompleted($student, $academicYear)
                            ]);
                            $updated++;
                        }
                    } else {
                        // Create new clearance
                        $clearance = Clearance::create([
                            'student_id' => $student->id,
                            'academic_id' => $academicYear->id,
                            'department_id' => $student->department_id,
                            'overall_status' => 'pending',
                            'is_archived' => false,
                            'previous_semester_completed' => $this->checkPreviousSemesterCompleted($student, $academicYear)
                        ]);

                        // Create clearance statuses for relevant departments
                        $this->createClearanceStatuses($clearance, $student, $allDepartments);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error creating clearance for student {$student->student_number}: " . $e->getMessage());
                    $errors++;
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();

        $this->info("\n\n✅ Academic year transition completed!");
        $this->info("📊 Summary:");
        $this->info("   • Students updated to new academic year: {$studentsUpdatedCount}");
        $this->info("   • Created: {$created} new clearances");
        $this->info("   • Updated: {$updated} existing clearances");
        $this->info("   • Errors: {$errors}");

        if ($created > 0 || $updated > 0) {
            $this->info("\n🎯 Next steps:");
            $this->info("   • Students can now access their clearance for {$academicYear->academic_year} - {$academicYear->semester}");
            $this->info("   • All clearance statuses are set to 'pending'");
            $this->info("   • Departments can begin processing clearances");
        }

        return 0;
    }

    /**
     * Show what would be created in dry run mode
     */
    private function showDryRunResults($students, $academicYear, $departments)
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Students to process', $students->count()],
                ['Academic Year', "{$academicYear->academic_year} - {$academicYear->semester}"],
                ['Departments requiring clearance', $departments->count()],
                ['Total clearance statuses to create', $students->count() * $departments->count()],
            ]
        );

        $this->info("\nDepartments that will require clearance:");
        foreach ($departments as $dept) {
            $this->info("   • {$dept->department_name} ({$dept->department_code})");
        }

        $this->info("\nSample students that would get clearances:");
        $sampleStudents = $students->take(5);
        foreach ($sampleStudents as $student) {
            $this->info("   • {$student->student_number} - {$student->user->firstname} {$student->user->lastname}");
        }

        if ($students->count() > 5) {
            $this->info("   ... and " . ($students->count() - 5) . " more students");
        }
    }

    /**
     * Create clearance statuses for a clearance
     */
    private function createClearanceStatuses($clearance, $student, $departments)
    {
        foreach ($departments as $department) {
            // Students only need clearance from their own academic department + service departments + PSG/SPUP
            $needsClearance = false;

            if (in_array($department->department_code, ['BAO', 'CLINIC', 'FOODLAB', 'LIBRARY', 'OSA', 'RESEARCH'])) {
                // All students need clearance from service departments
                $needsClearance = true;
            } elseif ($department->id == $student->department_id) {
                // Students need clearance from their own academic department
                $needsClearance = true;
            } elseif ($department->department_code === 'SPUP') {
                // SPUP (Governor) clearance for regular (non-UNIWIDE) students
                // For now, include for all students since student_type is not properly set
                $needsClearance = true;
            }

            if ($needsClearance) {
                ClearanceStatus::create([
                    'clearance_id' => $clearance->id,
                    'student_id' => $student->id,
                    'department_id' => $department->id,
                    'status' => 'pending',
                    'approved_by' => null,
                    'cleared_at' => null,
                ]);
            }
        }
    }

    /**
     * Check if student completed previous semester
     */
    private function checkPreviousSemesterCompleted($student, $currentAcademicYear)
    {
        // For first year students or new academic year, assume previous semester is completed
        if ($student->year === '1st' || $student->year === 'First Year' || $student->year === '1') {
            return true;
        }

        // Check if student has completed clearance in previous academic year/semester
        $previousClearance = Clearance::where('student_id', $student->id)
            ->where('academic_id', '<', $currentAcademicYear->id)
            ->where('overall_status', 'cleared')
            ->latest()
            ->first();

        return $previousClearance !== null;
    }
}
