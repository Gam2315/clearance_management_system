<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\Department;
use App\Models\AcademicYear;

class DepartmentSpecificClearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates clearance records with department-specific logic:
     * - Students only need clearance from their own academic department
     * - Plus common service departments (BAO, Clinic, Foodlab, Library, OSA, Research)
     * - NOT from other academic departments they don't belong to
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

        // Service departments that ALL students need clearance from
        $serviceDepartments = [5, 11, 12, 13, 14, 15, 23, 24, 25, 26, 27, 28, 29]; // BAO, Clinic, Foodlab, Library, OSA, Research, CF, Science Lab, Registrar, Boutique, Guidance, Engineering, Computer

        $this->command->info('Creating department-specific clearance records...');
        $this->command->info('Students will only need clearance from:');
        $this->command->info('• Their own academic department');
        $this->command->info('• Common service departments (BAO, Clinic, Foodlab, Library, OSA, Research, CF, Science Lab, Registrar, Boutique, Guidance, Engineering, Computer)');
        $this->command->newLine();

        foreach ($students as $student) {
            $this->command->info("Setting up clearance for: {$student->student_number} - {$student->user->firstname} {$student->user->lastname}");
            $this->command->info("  Student Department: {$student->department->department_name} (ID: {$student->department_id})");
            
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

            // Get required departments for this student
            $requiredDepartments = array_merge([$student->department_id], $serviceDepartments);
            
            $this->command->info("  → Required departments: " . count($requiredDepartments));

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

                $this->command->info("    • {$department->department_name}");
            }

            $this->command->newLine();
        }

        $this->command->info('✅ Department-specific clearance data created successfully!');
        $this->command->info('📋 Students now only have clearance requirements for relevant departments.');
        $this->command->info('🎯 Academic departments will only see their own students.');
        $this->command->info('🏢 Service departments will handle all students.');
    }
}
