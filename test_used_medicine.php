<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UsedMedicineView;

$medicine = UsedMedicineView::first();

if ($medicine) {
    echo "Raw Data:\n";
    print_r($medicine->toArray());

    echo "\n\nDirect Access:\n";
    echo "ID: " . $medicine->id . "\n";
    echo "Medicine ID: " . $medicine->medicine_id . "\n";
    echo "Medicine Name: " . ($medicine->medicine_name ?? 'NULL') . "\n";
    echo "Source: " . ($medicine->source ?? 'NULL') . "\n";
    echo "Quantity: " . ($medicine->quantity ?? 'NULL') . "\n";
    echo "Patient Name: " . ($medicine->patient_name ?? 'NULL') . "\n";
    echo "Used For: " . ($medicine->used_for ?? 'NULL') . "\n";

    echo "\n\nGetAttribute:\n";
    echo "Medicine Name (getAttribute): " . ($medicine->getAttribute('medicine_name') ?? 'NULL') . "\n";
    echo "Source (getAttribute): " . ($medicine->getAttribute('source') ?? 'NULL') . "\n";

    echo "\n\nAttributes Array:\n";
    print_r($medicine->getAttributes());
} else {
    echo "No data found\n";
}
