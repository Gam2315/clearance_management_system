<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--email= : Admin email address}
                            {--password= : Admin password}
                            {--firstname= : Admin first name}
                            {--lastname= : Admin last name}
                            {--employee-id= : Employee ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user for the clearance management system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Admin User for Clearance Management System');
        $this->info('================================================');

        // Get user input
        $email = $this->option('email') ?: $this->ask('Enter admin email');
        $password = $this->option('password') ?: $this->secret('Enter admin password');
        $firstname = $this->option('firstname') ?: $this->ask('Enter first name');
        $lastname = $this->option('lastname') ?: $this->ask('Enter last name');
        $employeeId = $this->option('employee-id') ?: $this->ask('Enter employee ID');

        // Validate input
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'employee_id' => $employeeId,
        ], [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:users,employee_id',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('- ' . $error);
            }
            return 1;
        }

        // Create admin user
        try {
            $admin = User::create([
                'name' => strtolower($firstname . '.' . $lastname),
                'firstname' => $firstname,
                'lastname' => $lastname,
                'middlename' => '',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'employee_id' => $employeeId,
                'department_id' => null,
            ]);

            $this->info('✅ Admin user created successfully!');
            $this->table(['Field', 'Value'], [
                ['Name', $admin->firstname . ' ' . $admin->lastname],
                ['Email', $admin->email],
                ['Employee ID', $admin->employee_id],
                ['Role', $admin->role],
                ['Status', $admin->status],
            ]);

            $this->info('You can now login at: ' . url('/auth/admin/login'));

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            return 1;
        }
    }
}
