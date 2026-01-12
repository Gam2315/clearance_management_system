<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\Department;
use App\Models\AcademicYear;

class RealisticClearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
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

        // Define which departments are required for clearance (remove duplicates)
        $requiredDepartments = [1, 2, 3, 4, 5, 11, 12, 13, 14, 15];

        foreach ($students as $student) {
            $this->command->info("Creating clearance for: {$student->student_number} - {$student->user->firstname} {$student->user->lastname}");
            
            // Delete existing clearance statuses for this student and academic year
            $existingClearance = Clearance::where('student_id', $student->id)
                ->where('academic_id', $academicYear->id)
                ->first();
                
            if ($existingClearance) {
                $existingClearance->statuses()->delete();
                $existingClearance->delete();
            }

            // Create new clearance record
            $clearance = Clearance::create([
                'student_id' => $student->id,
                'academic_id' => $academicYear->id,
                'department_id' => $student->department_id,
                'overall_status' => 'pending',
                'previous_semester_completed' => true,
            ]);

            $clearedCount = 0;

            // Create clearance status for each required department
            foreach ($requiredDepartments as $departmentId) {
                $department = Department::find($departmentId);
                if (!$department) continue;

                $status = 'pending';
                $clearedAt = null;
                $remarks = 'Awaiting clearance verification.';
                $approvedBy = null;

                // Realistic clearance logic based on department type
                if ($departmentId == $student->department_id) {
                    // Student's own academic department - usually cleared
                    $status = 'cleared';
                    $clearedAt = now()->subDays(rand(1, 7));
                    $remarks = 'All academic requirements completed.';
                    $clearedCount++;
                } elseif (in_array($departmentId, [13, 14])) {
                    // Library (13) and OSA (14) - commonly cleared early
                    if (rand(1, 100) <= 80) { // 80% chance of being cleared
                        $status = 'cleared';
                        $clearedAt = now()->subDays(rand(1, 5));
                        $remarks = 'No pending obligations.';
                        $clearedCount++;
                    }
                } elseif ($departmentId == 5) {
                    // BAO (Business Affairs Office) - financial clearance, often pending
                    if (rand(1, 100) <= 30) { // 30% chance of being cleared
                        $status = 'cleared';
                        $clearedAt = now()->subDays(rand(1, 3));
                        $remarks = 'All financial obligations settled.';
                        $clearedCount++;
                    } else {
                        $remarks = 'Pending financial clearance verification.';
                    }
                } elseif (in_array($departmentId, [11, 12])) {
                    // Clinic (11) and Foodlab (12) - health and lab clearances
                    if (rand(1, 100) <= 60) { // 60% chance of being cleared
                        $status = 'cleared';
                        $clearedAt = now()->subDays(rand(1, 4));
                        $remarks = 'Health/lab requirements completed.';
                        $clearedCount++;
                    }
                } else {
                    // Other academic departments - students shouldn't be cleared by departments they don't belong to
                    $status = 'pending';
                    $remarks = 'Not applicable to student\'s department.';
                }

                ClearanceStatus::create([
                    'clearance_id' => $clearance->id,
                    'department_id' => $departmentId,
                    'status' => $status,
                    'cleared_at' => $clearedAt,
                    'remarks' => $remarks,
                    'approved_by' => $approvedBy,
                    'student_id' => $student->id,
                ]);
            }

            // Update overall status
            $totalRequired = count($requiredDepartments);
            if ($clearedCount == $totalRequired) {
                $clearance->update(['overall_status' => 'cleared']);
            } else {
                $clearance->update(['overall_status' => 'pending']);
            }

            $this->command->info("  → Cleared: {$clearedCount}/{$totalRequired} departments");
        }

        $this->command->info('Realistic clearance data seeded successfully!');
    }
}
