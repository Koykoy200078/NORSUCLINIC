<?php

// Test script to check getAppLogo function
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing getAppLogo function:\n";

try {
    $logo = getAppLogo();
    echo "✓ getAppLogo() result: " . $logo . "\n";

    // Test if file exists
    $logoPath = public_path($logo);
    if (file_exists($logoPath)) {
        echo "✓ Logo file exists at: " . $logoPath . "\n";
    } else {
        echo "✗ Logo file NOT found at: " . $logoPath . "\n";
    }

    // Test getSettingValue for logo
    $logoSetting = getSettingValue('logo');
    echo "✓ getSettingValue('logo'): " . ($logoSetting ?: 'empty') . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
