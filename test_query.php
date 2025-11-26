<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UsedMedicineView;
use Illuminate\Support\Facades\Auth;

echo "Testing UsedMedicineView Query:\n\n";

// Get all patient names in the view
$allPatientNames = UsedMedicineView::select('patient_name')->distinct()->get();
echo "All patient names in used_medicines_view:\n";
foreach ($allPatientNames as $p) {
    echo "- '{$p->patient_name}'\n";
}

echo "\n\nAll records:\n";
$allRecords = UsedMedicineView::all();
foreach ($allRecords as $record) {
    echo "ID: {$record->id} | Medicine: '{$record->medicine_name}' | Source: '{$record->source}' | Patient: '{$record->patient_name}' | Qty: {$record->quantity}\n";
}

echo "\n\nTest Query with Patient Name:\n";
$testName = 'Christian Franc Carvajal';
$testResults = UsedMedicineView::where('patient_name', $testName)->get();
echo "Records for '{$testName}': " . $testResults->count() . "\n";
foreach ($testResults as $record) {
    echo "  - Medicine: '{$record->medicine_name}' | Source: '{$record->source}'\n";
}
