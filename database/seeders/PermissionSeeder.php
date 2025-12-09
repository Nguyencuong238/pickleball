<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        Permission::firstOrCreate(['name' => 'manage-users']);
        Permission::firstOrCreate(['name' => 'manage-permissions']);
        Permission::firstOrCreate(['name' => 'manage-courts']);
        Permission::firstOrCreate(['name' => 'manage-bookings']);
        Permission::firstOrCreate(['name' => 'manage-pages']);
        Permission::firstOrCreate(['name' => 'manage-stadiums']);
        Permission::firstOrCreate(['name' => 'manage-matches']);
        Permission::firstOrCreate(['name' => 'submit-scores']);

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $homeYardRole = Role::firstOrCreate(['name' => 'home_yard']);
        $refereeRole = Role::firstOrCreate(['name' => 'referee']);

        // Assign permissions to roles
        $adminRole->syncPermissions([
            'manage-users',
            'manage-permissions',
            'manage-courts',
            'manage-bookings',
            'manage-pages',
            'manage-stadiums',
            'manage-matches',
            'submit-scores',
        ]);

        $userRole->syncPermissions([
            'manage-bookings',
        ]);

        $homeYardRole->syncPermissions([
            'manage-courts',
            'manage-bookings',
            'manage-pages',
            'manage-stadiums',
        ]);

        $refereeRole->syncPermissions([
            'manage-matches',
            'submit-scores',
        ]);
    }
}
