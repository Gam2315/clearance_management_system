<?php

namespace Database\Factories;

use App\Models\Clearance;
use App\Models\Student;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClearanceFactory extends Factory
{
    protected $model = Clearance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'academic_id' => AcademicYear::factory(),
            'department_id' => Department::factory(),
            'overall_status' => $this->faker->randomElement(['pending', 'cleared', 'incomplete']),
            'is_archived' => false,
            'is_locked' => false,
            'lock_reason' => null,
            'locked_at' => null,
            'locked_by' => null,
            'semester' => $this->faker->randomElement(['1st', '2nd']),
            'previous_semester_completed' => true,
        ];
    }
}
