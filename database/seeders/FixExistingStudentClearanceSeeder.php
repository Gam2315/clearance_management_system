<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class FixExistingStudentClearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fixing clearance records for existing students...');

        // Get current academic year
        $academicYear = AcademicYear::where('status', 'active')->first();
        if (!$academicYear) {
            $this->command->error('No active academic year found!');
            return;
        }

        // Service departments that ALL students need clearance from
        $serviceDepartments = [5, 11, 12, 13, 14, 15, 23, 24, 25, 26, 27, 28, 29]; // BAO, Clinic, Foodlab, Library, OSA, Research, CF, Science Lab, Registrar, Boutique, Guidance, Engineering, Computer

        // Get all students
        $students = Student::with('user')->where('is_archived', false)->get();

        $this->command->info("Found {$students->count()} students to check...");

        foreach ($students as $student) {
            $this->command->info("Checking student: {$student->student_number} - {$student->user->firstname} {$student->user->lastname}");

            // Check if student has clearance record
            $clearance = Clearance::where('student_id', $student->id)
                ->where('academic_id', $academicYear->id)
                ->first();

            if (!$clearance) {
                // Create clearance record
                $clearance = Clearance::create([
                    'student_id' => $student->id,
                    'academic_id' => $academicYear->id,
                    'department_id' => $student->department_id,
                    'overall_status' => 'pending',
                    'previous_semester_completed' => true,
                ]);
                $this->command->info("  → Created clearance record");
            }

            // Get required departments for this specific student
            // Add SPUP (17) for UNIWIDE students
            $studentServiceDepartments = $serviceDepartments;
            if ($student->is_uniwide) {
                $studentServiceDepartments[] = 17; // SPUP for UNIWIDE students
            }

            // Student's own academic department + service departments
            $requiredDepartments = array_merge([$student->department_id], $studentServiceDepartments);

            // Delete existing incorrect clearance statuses and recreate them correctly
            ClearanceStatus::where('clearance_id', $clearance->id)->delete();
            $this->command->info("  → Deleted existing clearance statuses");

            // Create clearance status for each required department
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
            $this->command->info("  → Created " . count($requiredDepartments) . " clearance statuses for student's department and service departments");
        }

        $this->command->info('Finished fixing clearance records!');
    }
}
