<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\Department;
use App\Models\AcademicYear;

class PendingClearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates clearance records with ALL departments set to PENDING status.
     * Students must go through the actual clearance process to get cleared.
     */
    public function run(): void
    {
        // Get all students
        $students = Student::all();
        
        if ($students->isEmpty()) {
            $this->command->error('No students found to create clearances for.');
            return;
        }

        // Get the current academic year (2024-2025 2nd Semester)
        $academicYear = AcademicYear::find(4);
        
        if (!$academicYear) {
            $this->command->error('Academic year with ID 4 not found.');
            return;
        }

        // Define which departments are required for clearance
        $requiredDepartments = [1, 2, 3, 4, 5, 11, 12, 13, 14, 15];

        $this->command->info('Creating pending clearance records for all students...');
        $this->command->info('All departments will be set to PENDING status.');
        $this->command->info('Students must go through the actual clearance process to get cleared.');
        $this->command->newLine();

        foreach ($students as $student) {
            $this->command->info("Setting up clearance for: {$student->student_number} - {$student->user->firstname} {$student->user->lastname}");
            
            // Delete existing clearance and statuses for this student and academic year
            $existingClearance = Clearance::where('student_id', $student->id)
                ->where('academic_id', $academicYear->id)
                ->first();
                
            if ($existingClearance) {
                $existingClearance->statuses()->delete();
                $existingClearance->delete();
                $this->command->info("  → Removed existing clearance data");
            }

            // Create new clearance record
            $clearance = Clearance::create([
                'student_id' => $student->id,
                'academic_id' => $academicYear->id,
                'department_id' => $student->department_id,
                'overall_status' => 'pending',
                'previous_semester_completed' => true,
            ]);

            // Create clearance status for each required department - ALL SET TO PENDING
            foreach ($requiredDepartments as $departmentId) {
                $department = Department::find($departmentId);
                if (!$department) continue;

                ClearanceStatus::create([
                    'clearance_id' => $clearance->id,
                    'department_id' => $departmentId,
                    'status' => 'pending',
                    'cleared_at' => null,
                    'remarks' => 'Awaiting clearance verification.',
                    'approved_by' => null,
                    'student_id' => $student->id,
                ]);
            }

            $this->command->info("  → Created " . count($requiredDepartments) . " pending clearance statuses");
        }

        $this->command->newLine();
        $this->command->info('✅ Pending clearance data created successfully!');
        $this->command->info('📋 All students now have PENDING status for all departments.');
        $this->command->info('🎯 Students must now go through the actual clearance process:');
        $this->command->info('   • Tap ID at department kiosks');
        $this->command->info('   • Get manually cleared by department staff');
        $this->command->info('   • Follow proper clearance procedures');
    }
}
