<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clearance;
use App\Models\ClearanceStatus;
use App\Models\AcademicYear;

class CleanupDuplicateClearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the active academic year
        $activeAcademicYear = AcademicYear::where('status', 'active')->first();
        
        if (!$activeAcademicYear) {
            $this->command->error('No active academic year found.');
            return;
        }

        // Find all students with multiple clearance records
        $duplicateStudents = Clearance::select('student_id')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('student_id');

        foreach ($duplicateStudents as $studentId) {
            $this->command->info("Cleaning up clearance records for student ID: {$studentId}");
            
            // Get all clearance records for this student
            $clearances = Clearance::where('student_id', $studentId)->get();
            
            // Keep the one for the active academic year, or the most recent one
            $keepClearance = $clearances->where('academic_id', $activeAcademicYear->id)->first() 
                ?? $clearances->sortByDesc('created_at')->first();
            
            // Delete the others
            foreach ($clearances as $clearance) {
                if ($clearance->id !== $keepClearance->id) {
                    $this->command->info("Deleting clearance ID: {$clearance->id} for academic year: {$clearance->academic_id}");
                    
                    // Delete related clearance statuses first
                    ClearanceStatus::where('clearance_id', $clearance->id)->delete();
                    
                    // Delete the clearance record
                    $clearance->delete();
                }
            }
            
            $this->command->info("Kept clearance ID: {$keepClearance->id} for academic year: {$keepClearance->academic_id}");
        }

        $this->command->info('Duplicate clearance cleanup completed!');
    }
}
