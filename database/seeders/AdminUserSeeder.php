<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@pickleball.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123456'),
            ]
        );

        // Assign admin role
        $admin->syncRoles([$adminRole]);

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: admin@pickleball.com');
        $this->command->info('Password: admin123456');
    }
}
