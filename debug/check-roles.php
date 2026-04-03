<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n========== ROLES & PERMISSIONS ==========\n";
$roles = \App\Models\Role::with('permissions')->get();
foreach ($roles as $role) {
    echo "\n=== {$role->name} ({$role->display_name}) ===\n";
    if ($role->permissions->isEmpty()) {
        echo "  (no permissions)\n";
    }
    foreach ($role->permissions as $p) {
        echo "  - {$p->name}\n";
    }
}

echo "\n========== ALL PERMISSIONS ==========\n";
$perms = \App\Models\Permission::orderBy('name')->get();
foreach ($perms as $p) {
    echo "  [{$p->id}] {$p->name} - {$p->display_name}\n";
}

echo "\n========== ROUTES WITH MIDDLEWARE ==========\n";
$routes = app('router')->getRoutes();
foreach ($routes as $route) {
    $middleware = implode(', ', $route->gatherMiddleware());
    if (str_contains($middleware, 'permission') || str_contains($middleware, 'role')) {
        echo "  [{$route->methods()[0]}] /{$route->uri()} | {$middleware}\n";
    }
}
