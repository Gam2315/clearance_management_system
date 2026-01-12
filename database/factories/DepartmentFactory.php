<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'department_name' => $this->faker->randomElement([
                'College of Computer Studies',
                'College of Engineering',
                'College of Business Administration',
                'College of Arts and Sciences'
            ]),
            'department_code' => $this->faker->unique()->randomElement(['CCS', 'COE', 'CBA', 'CAS']),
            'status' => 'active',
        ];
    }
}
