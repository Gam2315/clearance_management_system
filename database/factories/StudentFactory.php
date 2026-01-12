<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'users_id' => User::factory(),
            'student_number' => $this->faker->unique()->numerify('####-####'),
            'course_id' => Course::factory(),
            'year' => $this->faker->randomElement(['1st', '2nd', '3rd', '4th']),
            'department_id' => Department::factory(),
            'academic_id' => AcademicYear::factory(),
            'nfc_uid' => $this->faker->optional()->uuid(),
            'is_archived' => false,
            'has_violations' => false,
            'is_graduated' => false,
            'clearance_history' => null,
            'first_year_clearance_completed' => null,
        ];
    }
}
