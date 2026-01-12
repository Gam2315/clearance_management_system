<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\ClearanceStatus;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixDuplicateDepartmentsSeeder extends Seeder
{
    /**
     * Fix duplicate departments and consolidate clearance statuses
     */
    public function run(): void
    {
        $this->command->info('🔧 Starting duplicate department cleanup...');
        
        // Define the correct department mappings
        $correctDepartments = [
            'SITE' => 1,
            'SASTE' => 2, 
            'SNAHS' => 3,
            'SBAHM' => 4,
            'BAO' => 5,
            'CLINIC' => 11,
            'FOODLAB' => 12,
            'LIBRARY' => 13,
            'OSA' => 14,
            'RESEARCH' => 15,
            'SPUP' => 17,
        ];

        // Find and fix duplicate departments
        foreach ($correctDepartments as $code => $correctId) {
            $this->fixDuplicateDepartment($code, $correctId);
        }

        $this->command->info('✅ Duplicate department cleanup completed!');
    }

    private function fixDuplicateDepartment($departmentCode, $correctId)
    {
        // Find all departments with this code
        $departments = Department::where('department_code', $departmentCode)->get();
        
        if ($departments->count() <= 1) {
            $this->command->info("✓ {$departmentCode}: No duplicates found");
            return;
        }

        $this->command->info("🔍 {$departmentCode}: Found {$departments->count()} duplicates");
        
        // Keep the correct department, remove others
        $correctDepartment = $departments->where('id', $correctId)->first();
        $duplicateDepartments = $departments->where('id', '!=', $correctId);

        if (!$correctDepartment) {
            $this->command->error("❌ Correct department ID {$correctId} not found for {$departmentCode}");
            return;
        }

        foreach ($duplicateDepartments as $duplicate) {
            $this->command->info("  → Consolidating duplicate ID {$duplicate->id} into correct ID {$correctId}");
            
            // Move all clearance statuses from duplicate to correct department
            $movedStatuses = ClearanceStatus::where('department_id', $duplicate->id)
                ->update(['department_id' => $correctId]);
            
            if ($movedStatuses > 0) {
                $this->command->info("    • Moved {$movedStatuses} clearance statuses");
            }

            // Move all users from duplicate to correct department
            $movedUsers = User::where('department_id', $duplicate->id)
                ->update(['department_id' => $correctId]);
            
            if ($movedUsers > 0) {
                $this->command->info("    • Moved {$movedUsers} users");
            }

            // Move all students from duplicate to correct department
            $movedStudents = Student::where('department_id', $duplicate->id)
                ->update(['department_id' => $correctId]);
            
            if ($movedStudents > 0) {
                $this->command->info("    • Moved {$movedStudents} students");
            }

            // Delete the duplicate department
            $duplicate->delete();
            $this->command->info("    • Deleted duplicate department ID {$duplicate->id}");
        }

        // Remove any duplicate clearance statuses for the same clearance and department
        $this->removeDuplicateClearanceStatuses($correctId, $departmentCode);
    }

    private function removeDuplicateClearanceStatuses($departmentId, $departmentCode)
    {
        // Find duplicate clearance statuses (same clearance_id and department_id)
        $duplicates = DB::select("
            SELECT clearance_id, department_id, COUNT(*) as count, 
                   GROUP_CONCAT(id ORDER BY updated_at DESC) as ids
            FROM clearance_statuses 
            WHERE department_id = ? 
            GROUP BY clearance_id, department_id 
            HAVING COUNT(*) > 1
        ", [$departmentId]);

        foreach ($duplicates as $duplicate) {
            $ids = explode(',', $duplicate->ids);
            $keepId = array_shift($ids); // Keep the most recently updated one
            $deleteIds = $ids;

            if (!empty($deleteIds)) {
                $deleted = ClearanceStatus::whereIn('id', $deleteIds)->delete();
                $this->command->info("    • Removed {$deleted} duplicate clearance statuses for {$departmentCode}");
            }
        }
    }
}
