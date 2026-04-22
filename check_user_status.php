<?php

use App\Models\User;

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
    echo "User: $email\n";
    echo "Status: " . ($user->status ? 'Active' : 'Inactive') . "\n";
    echo "Email Verified At: " . ($user->email_verified_at ?: 'Not Verified') . "\n";
    echo "-------------------\n";
}
