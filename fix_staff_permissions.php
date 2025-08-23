<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Foundation\Application')->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    \Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

use Spatie\Permission\Models\Role;

echo "=== FIXING STAFF PERMISSIONS ===\n";

$staffRole = Role::where('name', 'staff')->first();
if ($staffRole) {
    // Revoke admin dashboard permission from staff
    if ($staffRole->hasPermissionTo('manage_admin_dashboard')) {
        $staffRole->revokePermissionTo('manage_admin_dashboard');
        echo "✅ Revoked admin dashboard permission from staff role\n";
    } else {
        echo "ℹ️ Staff role already doesn't have admin dashboard permission\n";
    }

    // Ensure staff has staff dashboard permission
    if (!$staffRole->hasPermissionTo('manage_staff_dashboard')) {
        $staffRole->givePermissionTo('manage_staff_dashboard');
        echo "✅ Granted staff dashboard permission to staff role\n";
    } else {
        echo "ℹ️ Staff role already has staff dashboard permission\n";
    }

    echo "\n=== VERIFICATION ===\n";
    echo "Staff has admin dashboard permission: " . ($staffRole->hasPermissionTo('manage_admin_dashboard') ? 'YES (PROBLEM)' : 'NO (CORRECT)') . "\n";
    echo "Staff has staff dashboard permission: " . ($staffRole->hasPermissionTo('manage_staff_dashboard') ? 'YES (CORRECT)' : 'NO (PROBLEM)') . "\n";
} else {
    echo "❌ Staff role not found!\n";
}

echo "\n=== PERMISSION FIX COMPLETE ===\n";
