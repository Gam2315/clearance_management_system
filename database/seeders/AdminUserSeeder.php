<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $existingAdmin = User::where('email', 'admin@spup.edu.ph')->first();
        
        if (!$existingAdmin) {
            User::create([
                'name' => 'admin',
                'firstname' => 'Admin',
                'lastname' => 'User',
                'middlename' => '',
                'suffix_name' => null,
                'email' => 'admin@spup.edu.ph',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
                'employee_id' => 'ADMIN001',
                'department_id' => null,
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@spup.edu.ph');
            $this->command->info('Password: admin123');
            $this->command->info('Employee ID: ADMIN001');
        } else {
            $this->command->info('Admin user already exists!');
        }

        // Create additional admin users if needed
        $additionalAdmins = [
            [
                'name' => 'superadmin',
                'firstname' => 'Super',
                'lastname' => 'Admin',
                'middlename' => '',
                'email' => 'superadmin@spup.edu.ph',
                'password' => Hash::make('superadmin123'),
                'employee_id' => 'SUPER001',
            ],
        ];

        foreach ($additionalAdmins as $adminData) {
            $existing = User::where('email', $adminData['email'])->first();
            
            if (!$existing) {
                User::create(array_merge($adminData, [
                    'role' => 'admin',
                    'status' => 'active',
                    'department_id' => null,
                    'suffix_name' => null,
                ]));
                
                $this->command->info("Additional admin created: {$adminData['email']}");
            }
        }
    }
}
