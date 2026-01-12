<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\Department;
use App\Models\AcademicYear;

class SyncClearanceWithUpdatedDepartmentsSeeder extends Seeder
{
    /**
     * Sync existing clearances with updated department requirements
     */
    public function run(): void
    {
        $this->command->info('🔧 Syncing existing clearances with updated department requirements...');
        
        // Get active academic year
        $academicYear = AcademicYear::where('status', 'active')->first();
        
        if (!$academicYear) {
            $this->command->error('❌ No active academic year found!');
            return;
        }
        
        $this->command->info("📅 Using academic year: {$academicYear->academic_year}");
        
        // Get all students with clearances for the active academic year
        $students = Student::with(['clearances' => function($query) use ($academicYear) {
            $query->where('academic_id', $academicYear->id);
        }, 'courses', 'department'])->where('is_archived', false)->get();
        
        $this->command->info("👥 Found {$students->count()} active students");
        $this->command->newLine();
        
        $totalUpdated = 0;
        $totalAdded = 0;
        
        foreach ($students as $student) {
            $this->command->info("Checking: {$student->student_number} - {$student->user->firstname} {$student->user->lastname}");
            
            $clearance = $student->clearances->first();
            
            if (!$clearance) {
                $this->command->info("  ⚠️  No clearance found, skipping...");
                continue;
            }
            
            // Get what departments this student SHOULD have based on updated logic
            $requiredDepartments = $student->getRequiredDepartments();
            
            // Get what departments this student CURRENTLY has in clearance_statuses
            $existingDepartments = $clearance->statuses()->pluck('department_id')->toArray();
            
            // Find missing departments
            $missingDepartments = array_diff($requiredDepartments, $existingDepartments);
            
            if (empty($missingDepartments)) {
                $this->command->info("  ✅ All departments already present");
                continue;
            }
            
            $this->command->info("  📝 Adding " . count($missingDepartments) . " missing departments:");
            
            // Add missing departments
            foreach ($missingDepartments as $departmentId) {
                $department = Department::find($departmentId);
                
                if (!$department) {
                    $this->command->info("    ❌ Department ID {$departmentId} not found");
                    continue;
                }
                
                // Create clearance status for missing department
                ClearanceStatus::create([
                    'clearance_id' => $clearance->id,
                    'student_id' => $student->id,
                    'department_id' => $departmentId,
                    'status' => 'pending',
                    'cleared_at' => null,
                    'remarks' => 'Awaiting clearance verification.',
                    'approved_by' => null,
                ]);
                
                $this->command->info("    ✅ Added: {$department->department_code} - {$department->department_name}");
                $totalAdded++;
            }
            
            $totalUpdated++;
            $this->command->newLine();
        }
        
        $this->command->info("🎉 Sync completed!");
        $this->command->info("📊 Students updated: {$totalUpdated}");
        $this->command->info("📊 Departments added: {$totalAdded}");
    }
}
