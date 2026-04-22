<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Roles:\n";
foreach (Role::all() as $role) {
    echo "- Name: {$role->name}, Guard: {$role->guard_name}\n";
}

echo "\nPermissions:\n";
foreach (Permission::all() as $permission) {
    echo "- Name: {$permission->name}, Guard: {$permission->guard_name}\n";
}
