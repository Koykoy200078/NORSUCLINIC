<?php

use App\Repositories\PrescriptionRepository;

try {
    echo "Testing prescription repository directly...\n";

    $prescriptionRepo = new PrescriptionRepository(app());

    // Test getData method
    echo "Testing getData method for prescription ID 5...\n";
    $data = $prescriptionRepo->getData(5);

    if (isset($data['prescription'])) {
        $prescription = $data['prescription'];
        echo "✅ SUCCESS: Found prescription ID " . $prescription->id . "\n";
        echo "Patient: " . ($prescription->patient->user->full_name ?? 'N/A') . "\n";
        echo "Doctor: " . ($prescription->doctor->user->full_name ?? 'N/A') . "\n";
        echo "Medicine count: " . $prescription->getMedicine->count() . "\n";

        // Test each medicine relationship
        foreach ($prescription->getMedicine as $index => $prescriptionMedicine) {
            echo "Medicine " . ($index + 1) . ": ";
            if ($prescriptionMedicine->medicines) {
                echo $prescriptionMedicine->medicines->name;
                echo " (Dosage: " . ($prescriptionMedicine->dosage ?? 'N/A') . ")";
                echo "\n";
            } else {
                echo "No medicine data found\n";
            }
        }

        // Test getSettingList method
        echo "\nTesting getSettingList method...\n";
        $settings = $prescriptionRepo->getSettingList();
        echo "✅ SUCCESS: Found " . count($settings) . " settings\n";
    } else {
        echo "❌ ERROR: No prescription data found\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Repository test completed.\n";
