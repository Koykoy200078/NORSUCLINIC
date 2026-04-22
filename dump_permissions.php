<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = Role::with('permissions')->get();

foreach ($roles as $role) {
    echo "Role: {$role->name}\n";
    echo "Permissions: " . $role->permissions->pluck('name')->implode(', ') . "\n";
    echo "-------------------\n";
}

$allPermissions = Permission::pluck('name');
echo "Total Permissions in DB: " . $allPermissions->count() . "\n";
echo "All: " . $allPermissions->implode(', ') . "\n";
