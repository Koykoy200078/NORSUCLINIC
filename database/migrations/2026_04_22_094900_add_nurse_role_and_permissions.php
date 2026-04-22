<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'nurse' role and assigns appropriate permissions.
     *
     * Nurse permissions:
     *   - manage_patients           → read-only enforced in routes/views
     *   - manage_request_documents  → can create/view consultations (pre-consultation & consultation form)
     *   - manage_medicines          → can view medicine list and dispense (view dispensing page)
     *
     * Nurse CANNOT:
     *   - manage_staff, manage_doctors, manage_settings, manage_roles
     *   - manage_admin_dashboard (nurse uses staff_dashboard)
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure all required permissions exist (they should from base seeders)
        $permissionsNeeded = [
            'manage_patients',
            'manage_request_documents',
            'manage_medicines',
            'manage_staff_dashboard',
        ];

        foreach ($permissionsNeeded as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // Create the nurse role (guard_name 'web' matches the rest of the system)
        $nurseRole = Role::firstOrCreate(
            ['name' => 'nurse', 'guard_name' => 'web']
        );

        // Assign permissions
        $nurseRole->syncPermissions($permissionsNeeded);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     * Removes the nurse role (permissions themselves are preserved for other roles).
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $nurseRole = Role::where('name', 'nurse')->first();
        if ($nurseRole) {
            $nurseRole->syncPermissions([]);
            $nurseRole->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
