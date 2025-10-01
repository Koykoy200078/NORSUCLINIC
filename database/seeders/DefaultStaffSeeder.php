<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DefaultStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the staff role if it doesn't exist
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'display_name' => 'Staff']);

        // Generate 5 staff members
        $staffMembers = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'contact' => '1234567890',
                'gender' => User::MALE,
                'type' => User::STAFF,
                'email' => 'john.doe@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'country_code' => '63',
                'time_zone' => '0'
            ]
        ];

        foreach ($staffMembers as $staffData) {
            $user = User::create($staffData);
            $user->assignRole($staffRole);
        }

        // Exclude specific permissions for the staff role
        $excludedPermissions = [
            'manage_roles',
            'manage_currencies',
            'manage_cities',
            'manage_states',
            'manage_countries',
            'manage_admin_dashboard',  // Staff should not have admin dashboard access
        ];

        // Assign permissions to the staff role, excluding the specified ones
        $staffPermissions = Permission::whereNotIn('name', $excludedPermissions)->pluck('name');
        $staffRole->givePermissionTo($staffPermissions);
    }
}
