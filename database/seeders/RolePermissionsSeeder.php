<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder sets the default permissions for staff and doctor roles
     * based on the current production database configuration.
     * 
     * Last Updated: October 7, 2025
     * Source: Current role_has_permissions table in production database
     */
    public function run(): void
    {
        $this->command->info('Setting up role permissions...');

        // Define default permissions for each role based on current database state
        $rolePermissions = [
            'doctor' => [
                'manage_medicines',
                'manage_patients',
                'manage_request_documents',
                'manage_specialties',
            ],
            'staff' => [
                'manage_doctors',
                'manage_medicines',
                'manage_patients',
                'manage_request_documents',
                'manage_specialties',
                'manage_staff',
                'manage_staff_dashboard',
            ],
        ];

        // Process each role
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $this->assignPermissionsToRole($roleName, $permissionNames);
        }

        // Ensure clinic_admin has all permissions
        $this->assignAllPermissionsToAdmin();

        $this->command->info('Role permissions setup completed successfully!');
    }

    /**
     * Assign specific permissions to a role
     */
    private function assignPermissionsToRole(string $roleName, array $permissionNames): void
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            $this->command->warn("Role '{$roleName}' not found. Skipping...");
            return;
        }

        $this->command->info("Processing {$roleName} role...");

        // First, revoke all permissions from this role to ensure clean state
        $role->syncPermissions([]);

        // Then assign the specified permissions
        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($permissionNames as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if (!$permission) {
                $this->command->warn("  - Permission '{$permissionName}' not found. Skipping...");
                $skippedCount++;
                continue;
            }

            $role->givePermissionTo($permission);
            $assignedCount++;
        }

        $this->command->info("  ✓ Assigned {$assignedCount} permissions to {$roleName} role");
        if ($skippedCount > 0) {
            $this->command->warn("  ! Skipped {$skippedCount} missing permissions");
        }

        // Also update permissions for existing users with this role
        $this->updateUserPermissions($role, $permissionNames);
    }

    /**
     * Update permissions for all users with the specified role
     */
    private function updateUserPermissions(Role $role, array $permissionNames): void
    {
        $users = User::role($role->name)->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Sync user permissions to match role permissions
            $permissions = Permission::whereIn('name', $permissionNames)->get();

            foreach ($permissions as $permission) {
                if (!$user->hasPermissionTo($permission)) {
                    $user->givePermissionTo($permission);
                }
            }
        }

        $this->command->info("  ✓ Updated permissions for {$users->count()} user(s) with {$role->name} role");
    }

    /**
     * Ensure clinic_admin role has all available permissions
     */
    private function assignAllPermissionsToAdmin(): void
    {
        $adminRole = Role::where('name', 'clinic_admin')->first();

        if (!$adminRole) {
            $this->command->warn('Clinic Admin role not found. Skipping admin permission assignment...');
            return;
        }

        $this->command->info('Processing clinic_admin role...');

        // Get all permissions
        $allPermissions = Permission::pluck('name');

        // Sync all permissions to admin role
        $adminRole->syncPermissions($allPermissions);

        $this->command->info("  ✓ Assigned all {$allPermissions->count()} permissions to clinic_admin role");

        // Update admin users
        $adminUsers = User::role('clinic_admin')->get();
        if ($adminUsers->isNotEmpty()) {
            foreach ($adminUsers as $user) {
                $user->syncPermissions($allPermissions);
            }
            $this->command->info("  ✓ Updated permissions for {$adminUsers->count()} admin user(s)");
        }
    }
}
