<?php

/**
 * Script to test the RolePermissionsSeeder
 * Run: php test_role_permissions_seeder.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "\n";
echo "=== TESTING ROLE PERMISSIONS SEEDER ===\n";
echo str_repeat('=', 60) . "\n\n";

// Expected permissions for each role
// UPDATED: Doctor now has full CRUD access with 10 permissions
$expectedPermissions = [
    'doctor' => [
        'manage_appointments',
        'manage_doctor_sessions',
        'manage_doctors_holiday',
        'manage_medicines',
        'manage_patient_visits',
        'manage_patients',           // Added: Full patient management
        'manage_request_documents',
        'manage_services',           // Added: Full service management
        'manage_specialties',        // Added: Full specialty management
        'manage_transactions',
    ],
    'staff' => [
        'manage_appointments',
        'manage_doctor_sessions',
        'manage_doctors',
        'manage_doctors_holiday',
        'manage_medicines',
        'manage_patient_visits',
        'manage_patients',
        'manage_request_documents',
        'manage_services',
        'manage_specialties',
        'manage_staff',
        'manage_staff_dashboard',
        'manage_transactions',
    ],
];

// Test each role
$allTestsPassed = true;

foreach ($expectedPermissions as $roleName => $expected) {
    echo "Testing {$roleName} role:\n";
    echo str_repeat('-', 60) . "\n";

    $role = Role::where('name', $roleName)->first();

    if (!$role) {
        echo "❌ FAIL: Role '{$roleName}' not found\n\n";
        $allTestsPassed = false;
        continue;
    }

    $actual = $role->permissions->pluck('name')->sort()->values()->toArray();
    $expected = collect($expected)->sort()->values()->toArray();

    // Check if arrays match
    if ($actual === $expected) {
        echo "✅ PASS: All permissions match (" . count($actual) . " permissions)\n";

        // Show the permissions
        foreach ($actual as $perm) {
            echo "   ✓ {$perm}\n";
        }
    } else {
        echo "❌ FAIL: Permissions do not match\n\n";
        $allTestsPassed = false;

        // Show missing permissions
        $missing = array_diff($expected, $actual);
        if (!empty($missing)) {
            echo "Missing permissions:\n";
            foreach ($missing as $perm) {
                echo "   ✗ {$perm}\n";
            }
            echo "\n";
        }

        // Show extra permissions
        $extra = array_diff($actual, $expected);
        if (!empty($extra)) {
            echo "Extra permissions:\n";
            foreach ($extra as $perm) {
                echo "   + {$perm}\n";
            }
            echo "\n";
        }
    }

    echo "\n";
}

// Test clinic_admin has all permissions
echo "Testing clinic_admin role:\n";
echo str_repeat('-', 60) . "\n";

$adminRole = Role::where('name', 'clinic_admin')->first();
$allPermissions = Permission::count();

if (!$adminRole) {
    echo "❌ FAIL: clinic_admin role not found\n\n";
    $allTestsPassed = false;
} else {
    $adminPermissions = $adminRole->permissions->count();

    if ($adminPermissions === $allPermissions) {
        echo "✅ PASS: Admin has all {$allPermissions} permissions\n\n";
    } else {
        echo "❌ FAIL: Admin has {$adminPermissions}/{$allPermissions} permissions\n\n";
        $allTestsPassed = false;
    }
}

// Final result
echo str_repeat('=', 60) . "\n";
if ($allTestsPassed) {
    echo "✅ ALL TESTS PASSED!\n";
    echo "Role permissions are configured correctly.\n";
} else {
    echo "❌ SOME TESTS FAILED!\n";
    echo "Please run: php artisan db:seed --class=RolePermissionsSeeder\n";
}
echo str_repeat('=', 60) . "\n\n";
