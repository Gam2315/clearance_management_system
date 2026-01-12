<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\ClearanceStatus;
use App\Models\Clearance;
use App\Models\Student;

class AddNewDepartmentsToClearanceSeeder extends Seeder
{
    /**
     * Add new departments to existing student clearances
     */
    public function run(): void
    {
        $this->command->info('🔧 Adding new departments to student clearances...');
        
        // New departments to add to clearance system
        $newDepartmentIds = [23, 24, 25, 26, 27, 28, 29];
        
        // Verify all departments exist
        foreach ($newDepartmentIds as $deptId) {
            $dept = Department::find($deptId);
            if (!$dept) {
                $this->command->error("❌ Department ID {$deptId} not found!");
                return;
            }
            $this->command->info("✓ Found: {$dept->department_code} - {$dept->department_name}");
        }

        // Get all existing clearances
        $clearances = Clearance::with('student')->get();
        $this->command->info("\n📋 Found {$clearances->count()} existing clearances");

        $totalAdded = 0;

        foreach ($clearances as $clearance) {
            $student = $clearance->student;
            $this->command->info("\n👤 Processing: {$student->student_number} ({$student->user->firstname} {$student->user->lastname})");

            foreach ($newDepartmentIds as $deptId) {
                $dept = Department::find($deptId);
                
                // Check if clearance status already exists
                $existingStatus = ClearanceStatus::where('clearance_id', $clearance->id)
                    ->where('department_id', $deptId)
                    ->first();

                if ($existingStatus) {
                    $this->command->info("  ⚠️  {$dept->department_code}: Already exists");
                    continue;
                }

                // Create new pending clearance status
                ClearanceStatus::create([
                    'clearance_id' => $clearance->id,
                    'student_id' => $student->id,
                    'department_id' => $deptId,
                    'status' => 'pending',
                    'cleared_at' => null,
                    'remarks' => 'Awaiting clearance verification.',
                    'approved_by' => null,
                ]);

                $this->command->info("  ✅ {$dept->department_code}: Added as pending");
                $totalAdded++;
            }
        }

        $this->command->info("\n🎉 Successfully added {$totalAdded} new clearance statuses!");
        $this->command->info("📊 Summary of new departments added:");
        
        foreach ($newDepartmentIds as $deptId) {
            $dept = Department::find($deptId);
            $count = ClearanceStatus::where('department_id', $deptId)->count();
            $this->command->info("  • {$dept->department_code}: {$count} students");
        }
    }
}
