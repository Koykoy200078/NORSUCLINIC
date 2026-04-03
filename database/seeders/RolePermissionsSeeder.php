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
     * Permission Rules:
     *  - clinic_admin : ALL permissions (manages everything including staff accounts)
     *  - staff        : Can manage doctors, patients, medicines, specialties, requests
     *                   CANNOT manage staff accounts (admin-only)
     *  - doctor       : Can manage patients, medicines, specialties, requests
     *                   CANNOT manage doctors or staff accounts
     *  - patient      : manage_request_documents only
     *
     * Last Updated: 2026-04-03
     */
    public function run(): void
    {
        $this->command->info('Setting up role permissions...');

        // Define default permissions for each role
        $rolePermissions = [
            'doctor' => [
                // Doctors can view/manage their own patients, medicines, specialties, documents
                // They CANNOT create/edit/delete other doctor accounts (no manage_doctors)
                // They CANNOT manage staff accounts (no manage_staff)
                'manage_medicines',
                'manage_patients',
                'manage_request_documents',
                'manage_specialties',
            ],
            'staff' => [
                // Staff can create/edit/delete doctor accounts
                // Staff CANNOT manage other staff accounts (clinic_admin only)
                'manage_doctors',
                'manage_medicines',
                'manage_patients',
                'manage_request_documents',
                'manage_specialties',
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
