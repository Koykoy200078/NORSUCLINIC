<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emails = ['admin@norsuclinic.com', 'kycenomano@mailinator.com', 'mekek@mailinator.com'];

foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    if (!$user) {
        echo "User $email not found.\n";
        continue;
    }
    echo "User: $email (ID: {$user->id})\n";
    echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
    echo "Permissions count: " . $user->getAllPermissions()->count() . "\n";
    echo "-------------------\n";
}
