<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class CreateMissingDepartmentsSeeder extends Seeder
{
    /**
     * Create missing departments needed for clearance system
     */
    public function run(): void
    {
        $this->command->info('🔧 Creating missing departments for clearance system...');
        
        // Define all departments that should exist based on the student profile mapping
        $departments = [
            // Academic departments (1-4) - should already exist
            1 => ['SITE', 'SCHOOL OF INFORMATION TECHNOLOGY AND ENGINEERING'],
            2 => ['SASTE', 'SCHOOL OF ARTS, SCIENCES AND TEACHER EDUCATION'],
            3 => ['SNAHS', 'SCHOOL OF NURSING AND ALLIED HEALTH SCIENCES'],
            4 => ['SBAHM', 'SCHOOL OF BUSINESS, ACCOUNTANCY AND HOSPITALITY MANAGEMENT'],
            
            // Service departments
            5 => ['BAO', 'BUSINESS AFFAIRS OFFICE'],
            11 => ['CLINIC', 'CLINIC'],
            12 => ['FOODLAB', 'FOODLAB'],
            13 => ['LIBRARY', 'LIBRARY'],
            14 => ['OSA', 'OFFICE OF STUDENT AFFAIRS'],
            15 => ['RESEARCH', 'RESEARCH'],
            17 => ['SPUP', 'SPUP UNIWIDE'],
            23 => ['CHRISTIAN FORMATION', 'CHRISTIAN FORMATION'],
            24 => ['COLLEGE SCIENCE LAB', 'COLLEGE SCIENCE LAB'],
            25 => ['UNIVERSITY REGISTRAR', 'UNIVERSITY REGISTRAR'],
            26 => ['BOUTIQUE', 'BOUTIQUE'],
            27 => ['GUIDANCE', 'GUIDANCE'],
            28 => ['ENGINEERING', 'ENGINEERING'],
            29 => ['COMPUTER', 'COMPUTER'],
        ];
        
        $created = 0;
        $existing = 0;
        
        foreach ($departments as $id => $data) {
            [$code, $name] = $data;
            
            // Check if department already exists
            $existingDept = Department::find($id);
            
            if ($existingDept) {
                $this->command->info("✓ Department ID {$id} already exists: {$existingDept->department_code}");
                $existing++;
                continue;
            }
            
            // Create the department with specific ID
            DB::table('departments')->insert([
                'id' => $id,
                'department_code' => $code,
                'department_name' => $name,
                'department_head' => 'N/A',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info("✅ Created department ID {$id}: {$code} - {$name}");
            $created++;
        }
        
        $this->command->info("🎉 Department creation completed!");
        $this->command->info("📊 Existing departments: {$existing}");
        $this->command->info("📊 Created departments: {$created}");
        
        // Reset auto-increment to continue from the highest ID
        $maxId = max(array_keys($departments));
        DB::statement("ALTER TABLE departments AUTO_INCREMENT = " . ($maxId + 1));
        $this->command->info("📊 Set auto-increment to " . ($maxId + 1));
    }
}
