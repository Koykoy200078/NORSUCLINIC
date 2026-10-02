<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionsSeeder extends Seeder
{
    /** Setting that remembers which default role -> permission pairs this seeder has already applied. */
    private const APPLIED_KEY = 'default_role_permissions_applied';

    /**
     * Run the database seeds.
     *
     * CENTRAL AUTHORITY for the DEFAULT role-permission assignments.
     * When adding a new permission, create it in the relevant permission
     * seeder first, then add it here for the roles that need it.
     *
     * Permission Rules:
     *  - clinic_admin : ALL permissions (manages everything including staff accounts)
     *  - staff        : Can manage doctors, patients, medicines, specialties, requests
     *                   CANNOT manage staff accounts (admin-only)
     *  - doctor       : Can manage patients, medicines, specialties, requests
     *                   CANNOT manage doctors or staff accounts
     *  - patient      : manage_request_documents only
     *
     * Re-running `php artisan db:seed` is SAFE for permissions the clinic administrator changed in the Roles
     * screen: every default pair is applied ONCE (and remembered), so a permission the administrator took away
     * from a role is not given back, and a new default permission is added to its roles the first time it appears.
     * It used to wipe every role (`syncPermissions([])`) and give each user their own direct copy of the defaults,
     * which undid the administrator's changes and made later revocations ineffective for those users.
     *
     * Last Updated: 2026-10-02
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

        $applied = $this->loadApplied();

        // Process each role
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $this->assignPermissionsToRole($roleName, $permissionNames, $applied);
        }

        $this->saveApplied($applied);

        // Ensure clinic_admin has all permissions
        $this->assignAllPermissionsToAdmin();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Role permissions setup completed successfully!');
    }

    /**
     * Give a role the default permissions it has not been given before.
     *
     * @param  array<string, array<int, string>>  $applied  role => permissions already applied (updated here)
     */
    private function assignPermissionsToRole(string $roleName, array $permissionNames, array &$applied): void
    {
        $role = Role::where('name', $roleName)->first();

        if (! $role) {
            $this->command->warn("Role '{$roleName}' not found. Skipping...");

            return;
        }

        $this->command->info("Processing {$roleName} role...");

        $alreadyApplied = $applied[$roleName] ?? null;

        // A clinic that was set up before this was remembered and already has people in this role: whatever the role
        // has now IS its configuration. Record the defaults as applied without touching anything. (A brand new
        // install has no staff / doctor accounts yet, and its roles may already hold a permission or two from the
        // earlier seeders, so it still gets the full defaults below.)
        if ($alreadyApplied === null && User::role($roleName)->exists()) {
            $applied[$roleName] = $permissionNames;
            $this->command->info("  = {$roleName} already has accounts; left as configured");

            return;
        }

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($permissionNames as $permissionName) {
            if (in_array($permissionName, $alreadyApplied ?? [], true)) {
                continue;
            }

            $permission = Permission::where('name', $permissionName)->first();

            if (! $permission) {
                $this->command->warn("  - Permission '{$permissionName}' not found. Skipping...");
                $skippedCount++;

                continue;
            }

            $role->givePermissionTo($permission);
            $applied[$roleName][] = $permissionName;
            $assignedCount++;
        }

        $applied[$roleName] = array_values(array_unique($applied[$roleName] ?? []));

        $this->command->info("  ✓ Added {$assignedCount} new default permission(s) to {$roleName}");
        if ($skippedCount > 0) {
            $this->command->warn("  ! Skipped {$skippedCount} missing permissions");
        }
    }

    /**
     * Ensure clinic_admin role has all available permissions
     */
    private function assignAllPermissionsToAdmin(): void
    {
        $adminRole = Role::where('name', 'clinic_admin')->first();

        if (! $adminRole) {
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

    /** @return array<string, array<int, string>> */
    private function loadApplied(): array
    {
        $raw = Setting::where('key', self::APPLIED_KEY)->value('value');
        $decoded = $raw ? json_decode($raw, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    /** @param  array<string, array<int, string>>  $applied */
    private function saveApplied(array $applied): void
    {
        Setting::updateOrCreate(['key' => self::APPLIED_KEY], ['value' => json_encode($applied)]);
    }
}
