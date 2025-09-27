<?php

// Bootstrap Laravel
require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing prescription data...\n";

try {
    // Test getting prescription data directly
    $prescription = App\Models\Prescription::with([
        'patient.user',
        'doctor.user',
        'getMedicine.medicines'
    ])->find(5);

    if ($prescription) {
        echo "✅ Found prescription ID: " . $prescription->id . "\n";
        echo "Patient: " . $prescription->patient->user->full_name . "\n";
        echo "Doctor: " . $prescription->doctor->user->full_name . "\n";
        echo "Medicine count: " . $prescription->getMedicine->count() . "\n";

        foreach ($prescription->getMedicine as $index => $prescriptionMedicine) {
            echo "Medicine " . ($index + 1) . ": ";
            if ($prescriptionMedicine->medicines) {
                echo $prescriptionMedicine->medicines->name . " (Type: " . get_class($prescriptionMedicine->medicines) . ")\n";
            } else {
                echo "No medicine data\n";
            }
        }

        echo "\n✅ SUCCESS: Prescription data loaded correctly!\n";
    } else {
        echo "❌ ERROR: Prescription with ID 5 not found\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
