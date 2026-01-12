<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\Department;
use App\Models\AcademicYear;

class StudentClearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the student
        $student = Student::first();
        
        if (!$student) {
            $this->command->error('No student found to create clearance for.');
            return;
        }

        // Get the active academic year
        $academicYear = AcademicYear::where('status', 'active')->first();
        
        if (!$academicYear) {
            $this->command->error('No active academic year found.');
            return;
        }

        // Check if clearance already exists for this student and academic year
        $existingClearance = Clearance::where('student_id', $student->id)
            ->where('academic_id', $academicYear->id)
            ->first();

        if ($existingClearance) {
            $this->command->info('Clearance already exists for this student and academic year. Skipping...');
            return;
        }

        // Create clearance record
        $clearance = Clearance::create([
            'student_id' => $student->id,
            'academic_id' => $academicYear->id,
            'department_id' => $student->department_id,
            'overall_status' => 'pending',
        ]);

        // Define which departments are required for clearance
        $requiredDepartments = [1, 2, 3, 4, 5, 11, 12, 13, 14, 15]; // Remove duplicates (6, 7, 8, 9)

        // Create clearance status only for required departments
        foreach ($requiredDepartments as $departmentId) {
            $department = Department::find($departmentId);
            if (!$department) continue;

            $status = 'pending';
            $clearedAt = null;
            $remarks = null;

            // Realistic clearance logic:
            // 1. Students are cleared by their own academic department
            // 2. Some service departments (Library, OSA) might clear students easily
            // 3. Other departments remain pending for demo

            if ($departmentId == $student->department_id) {
                // Student's own department - cleared
                $status = 'cleared';
                $clearedAt = now()->subDays(rand(1, 5));
                $remarks = 'All academic requirements completed.';
            } elseif (in_array($departmentId, [13, 14])) {
                // Library and OSA - commonly cleared first
                $status = 'cleared';
                $clearedAt = now()->subDays(rand(1, 3));
                $remarks = 'No pending obligations.';
            } else {
                // Other departments remain pending
                $status = 'pending';
                $remarks = 'Awaiting clearance verification.';
            }

            ClearanceStatus::updateOrCreate(
                [
                    'clearance_id' => $clearance->id,
                    'department_id' => $departmentId,
                ],
                [
                    'status' => $status,
                    'cleared_at' => $clearedAt,
                    'remarks' => $remarks,
                ]
            );
        }

        // Update overall status based on individual statuses
        $totalStatuses = ClearanceStatus::where('clearance_id', $clearance->id)->count();
        $clearedStatuses = ClearanceStatus::where('clearance_id', $clearance->id)
            ->where('status', 'cleared')
            ->count();

        if ($clearedStatuses == $totalStatuses) {
            $clearance->update(['overall_status' => 'cleared']);
        } else {
            $clearance->update(['overall_status' => 'pending']);
        }

        $this->command->info('Student clearance seeded successfully!');
        $this->command->info("Student: {$student->student_number}");
        $this->command->info("Academic Year: {$academicYear->academic_year} - {$academicYear->semester}");
        $this->command->info("Overall Status: {$clearance->overall_status}");
        $this->command->info("Cleared Departments: {$clearedStatuses}/{$totalStatuses}");
    }
}
