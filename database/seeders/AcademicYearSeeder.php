<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = [
            [
                'academic_year' => '2023-2024',
                'semester' => '1st Semester',
                'status' => 'inactive',
            ],
            [
                'academic_year' => '2023-2024',
                'semester' => '2nd Semester',
                'status' => 'inactive',
            ],
            [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'inactive',
            ],
            [
                'academic_year' => '2024-2025',
                'semester' => '2nd Semester',
                'status' => 'active', // Current active semester
            ],
        ];

        foreach ($academicYears as $academicYear) {
            AcademicYear::updateOrCreate(
                [
                    'academic_year' => $academicYear['academic_year'],
                    'semester' => $academicYear['semester']
                ],
                $academicYear
            );
        }

        $this->command->info('Academic years seeded successfully!');
    }
}
