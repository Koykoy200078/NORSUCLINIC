<?php

/**
 * Script to extract current doctor and staff permissions from database
 * Run: php get_current_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== CURRENT PERMISSIONS IN DATABASE ===\n\n";

// Get all roles
$roles = Role::with('permissions')->whereIn('name', ['staff', 'doctor'])->get();

foreach ($roles as $role) {
    echo "ROLE: {$role->name}\n";
    echo str_repeat('=', 50) . "\n";

    $permissions = $role->permissions->pluck('name')->sort()->values()->toArray();

    echo "Total Permissions: " . count($permissions) . "\n\n";

    echo "Permissions Array for Seeder:\n";
    echo "[\n";
    foreach ($permissions as $permission) {
        echo "    '{$permission}',\n";
    }
    echo "]\n\n";

    echo str_repeat('-', 50) . "\n\n";
}

// Also show all available permissions
echo "\nALL AVAILABLE PERMISSIONS:\n";
echo str_repeat('=', 50) . "\n";
$allPermissions = Permission::orderBy('name')->pluck('name')->toArray();
echo "Total: " . count($allPermissions) . "\n\n";
foreach ($allPermissions as $perm) {
    echo "- {$perm}\n";
}
