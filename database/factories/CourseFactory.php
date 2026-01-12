<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'course_name' => $this->faker->randomElement([
                'Bachelor of Science in Computer Science',
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Civil Engineering',
                'Bachelor of Science in Business Administration'
            ]),
            'course_code' => $this->faker->unique()->randomElement(['BSCS', 'BSIT', 'BSCE', 'BSBA']),
            'department_id' => Department::factory(),
        ];
    }
}
