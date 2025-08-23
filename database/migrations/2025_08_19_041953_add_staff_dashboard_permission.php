<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the staff dashboard permission
        $permission = Permission::firstOrCreate([
            'name' => 'manage_staff_dashboard',
            'display_name' => 'Manage Staff Dashboard',
        ]);

        // Assign the permission to staff role
        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole && !$staffRole->hasPermissionTo('manage_staff_dashboard')) {
            $staffRole->givePermissionTo('manage_staff_dashboard');
        }

        // Ensure staff role doesn't have admin dashboard permission
        if ($staffRole && $staffRole->hasPermissionTo('manage_admin_dashboard')) {
            $staffRole->revokePermissionTo('manage_admin_dashboard');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the staff dashboard permission
        $permission = Permission::where('name', 'manage_staff_dashboard')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
