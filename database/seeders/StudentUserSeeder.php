<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class StudentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update the existing student user with a known password
        $studentUser = User::where('role', 'student')->first();
        
        if ($studentUser) {
            $studentUser->update([
                'password' => Hash::make('student123'),
                'email' => 'student@spup.edu.ph',
            ]);
            
            $this->command->info('Student user password updated successfully!');
            $this->command->info('Student Number: ' . $studentUser->student->student_number ?? 'N/A');
            $this->command->info('Email: student@spup.edu.ph');
            $this->command->info('Password: student123');
        } else {
            // Create a new student user if none exists
            $user = User::create([
                'name' => 'Test Student',
                'firstname' => 'Test',
                'lastname' => 'Student',
                'middlename' => 'S.',
                'email' => 'student@spup.edu.ph',
                'password' => Hash::make('student123'),
                'role' => 'student',
                'status' => 'active',
            ]);

            // Create corresponding student record
            Student::create([
                'users_id' => $user->id,
                'student_number' => '2024-01-0001',
                'department_id' => 1, // SITE
                'course_id' => 1, // BSIT
                'year' => '3rd',
                'academic_id' => 4, // Current active academic year
            ]);

            $this->command->info('New student user created successfully!');
            $this->command->info('Student Number: 2024-01-0001');
            $this->command->info('Email: student@spup.edu.ph');
            $this->command->info('Password: student123');
        }
    }
}
