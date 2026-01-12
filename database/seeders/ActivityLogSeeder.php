<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Department;
use Spatie\Activitylog\Models\Activity;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to create activity logs for
        $admin = User::where('role', 'admin')->first();
        $users = User::take(3)->get();

        if ($admin) {
            // Create some sample activity logs
            Activity::create([
                'log_name' => 'user',
                'description' => 'Admin user logged into the system.',
                'subject_type' => User::class,
                'subject_id' => $admin->id,
                'causer_type' => User::class,
                'causer_id' => $admin->id,
                'properties' => json_encode([]),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ]);

            Activity::create([
                'log_name' => 'user',
                'description' => 'A new admin account was created for Admin User.',
                'subject_type' => User::class,
                'subject_id' => $admin->id,
                'causer_type' => User::class,
                'causer_id' => $admin->id,
                'properties' => json_encode([]),
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ]);

            Activity::create([
                'log_name' => 'system',
                'description' => 'Database was seeded with initial data.',
                'subject_type' => null,
                'subject_id' => null,
                'causer_type' => null,
                'causer_id' => null,
                'properties' => json_encode([]),
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ]);

            Activity::create([
                'log_name' => 'department',
                'description' => 'Departments were populated in the system.',
                'subject_type' => Department::class,
                'subject_id' => 1,
                'causer_type' => User::class,
                'causer_id' => $admin->id,
                'properties' => json_encode([]),
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(15),
            ]);
        }

        $this->command->info('Activity logs seeded successfully!');
    }
}
