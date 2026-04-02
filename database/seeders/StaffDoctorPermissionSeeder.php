<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class StaffDoctorPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder ensures that both staff and doctor roles have access to key clinic management functions.
     */
    public function run(): void
    {
        // Define the permissions that both staff and doctor should have
        $sharedPermissions = [
            'manage_doctors',
            'manage_doctors_holiday',
            'manage_specialties',
            'manage_request_documents',
            'manage_medicines',
            'manage_patients',
            'manage_patient_visits',
        ];

        // Get staff and doctor roles
        $staffRole = Role::where('name', 'staff')->first();
        $doctorRole = Role::where('name', 'doctor')->first();

        if (!$staffRole) {
            $this->command->warn('Staff role not found. Creating staff role...');
            $staffRole = Role::create([
                'name' => 'staff',
                'display_name' => 'Staff',
                'is_default' => true,
                'guard_name' => 'web'
            ]);
        }

        if (!$doctorRole) {
            $this->command->warn('Doctor role not found. Creating doctor role...');
            $doctorRole = Role::create([
                'name' => 'doctor',
                'display_name' => 'Doctor',
                'is_default' => true,
                'guard_name' => 'web'
            ]);
        }

        // Assign permissions to both roles
        foreach ($sharedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if (!$permission) {
                $this->command->warn("Permission '{$permissionName}' not found. Skipping...");
                continue;
            }

            // Assign to staff role
            if (!$staffRole->hasPermissionTo($permission)) {
                $staffRole->givePermissionTo($permission);
                $this->command->info("Granted '{$permissionName}' permission to staff role");
            }

            // Assign to doctor role  
            if (!$doctorRole->hasPermissionTo($permission)) {
                $doctorRole->givePermissionTo($permission);
                $this->command->info("Granted '{$permissionName}' permission to doctor role");
            }
        }

        // Also assign permissions to existing users with these roles
        $this->assignPermissionsToUsers($staffRole, $sharedPermissions);
        $this->assignPermissionsToUsers($doctorRole, $sharedPermissions);

        $this->command->info('Staff and Doctor permission assignment completed successfully!');
    }

    /**
     * Assign permissions to existing users with the specified role
     */
    private function assignPermissionsToUsers(Role $role, array $permissions): void
    {
        $users = User::role($role->name)->get();

        foreach ($users as $user) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();

                if ($permission && !$user->hasPermissionTo($permission)) {
                    $user->givePermissionTo($permission);
                }
            }
        }

        $this->command->info("Updated permissions for {$users->count()} users with {$role->name} role");
    }
}
