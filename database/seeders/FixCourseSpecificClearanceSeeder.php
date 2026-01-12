<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\ClearanceStatus;
use App\Models\Clearance;
use App\Models\Student;
use App\Models\Course;

class FixCourseSpecificClearanceSeeder extends Seeder
{
    /**
     * Fix clearances to be course-specific for certain departments
     */
    public function run(): void
    {
        $this->command->info('🔧 Fixing course-specific clearance requirements...');
        
        // Define course-specific department requirements
        $courseSpecificDepartments = [
            12 => [ // FOODLAB - Only for SBAHM students
                'departments' => [4], // SBAHM
                'courses' => [], // All SBAHM courses
                'name' => 'FOODLAB'
            ],
            29 => [ // Computer - Only for SITE IT/Computer Engineering students
                'departments' => [1], // SITE
                'courses' => ['BSIT', 'BSCpE'], // Information Technology & Computer Engineering
                'name' => 'Computer'
            ],
            28 => [ // Engineering - Only for SITE engineering students
                'departments' => [1], // SITE
                'courses' => ['BSCE', 'BSEnSE', 'BSCpE'], // Civil, Environmental, Computer Engineering
                'name' => 'Engineering'
            ],
            24 => [ // Science Lab - Only for SASTE science students
                'departments' => [2], // SASTE
                'courses' => ['BSBio', 'BSBio-MicroBiology', 'BSPsych'], // Biology, Microbiology, Psychology
                'name' => 'Science Lab'
            ]
        ];

        // Get all existing clearances
        $clearances = Clearance::with(['student.courses', 'student.department'])->get();
        $this->command->info("📋 Found {$clearances->count()} existing clearances");

        $removedCount = 0;
        $keptCount = 0;

        foreach ($clearances as $clearance) {
            $student = $clearance->student;
            $studentDept = $student->department_id;
            $studentCourse = $student->courses->course_code;
            
            $this->command->info("\n👤 Processing: {$student->student_number} ({$student->user->firstname} {$student->user->lastname})");
            $this->command->info("  Department: {$student->department->department_code}, Course: {$studentCourse}");

            foreach ($courseSpecificDepartments as $deptId => $rules) {
                $deptName = $rules['name'];
                
                // Check if student has clearance status for this department
                $existingStatus = ClearanceStatus::where('clearance_id', $clearance->id)
                    ->where('department_id', $deptId)
                    ->first();

                if (!$existingStatus) {
                    continue; // No status for this department
                }

                // Check if student should have this department
                $shouldHaveDepartment = false;

                // Check department requirement
                if (in_array($studentDept, $rules['departments'])) {
                    // If no specific courses defined, all students in department qualify
                    if (empty($rules['courses'])) {
                        $shouldHaveDepartment = true;
                    } else {
                        // Check if student's course is in the allowed courses
                        if (in_array($studentCourse, $rules['courses'])) {
                            $shouldHaveDepartment = true;
                        }
                    }
                }

                if ($shouldHaveDepartment) {
                    $this->command->info("  ✅ {$deptName}: Student qualifies - keeping clearance status");
                    $keptCount++;
                } else {
                    $this->command->info("  ❌ {$deptName}: Student doesn't qualify - removing clearance status");
                    $existingStatus->delete();
                    $removedCount++;
                }
            }
        }

        $this->command->info("\n🎉 Course-specific clearance fix completed!");
        $this->command->info("📊 Summary:");
        $this->command->info("  • Kept: {$keptCount} appropriate clearance statuses");
        $this->command->info("  • Removed: {$removedCount} inappropriate clearance statuses");
        
        // Show current status for each course-specific department
        $this->command->info("\n📋 Current students per course-specific department:");
        foreach ($courseSpecificDepartments as $deptId => $rules) {
            $count = ClearanceStatus::where('department_id', $deptId)->count();
            $this->command->info("  • {$rules['name']}: {$count} students");
        }
    }
}
