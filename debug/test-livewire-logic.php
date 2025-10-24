<?php

// Test the exact logic used in the Livewire component
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RequestDocuments;

echo "=== TESTING LIVEWIRE COMPONENT LOGIC ===\n\n";

$consultations = RequestDocuments::where('document_type', 'consultation_form')
    ->orderBy('id')
    ->get();

echo "Testing the EXACT logic from RequestDocumentTable.php:\n";
echo str_repeat("-", 80) . "\n\n";

foreach ($consultations as $row) {
    echo "ID: {$row->id} | Patient: {$row->name}\n";

    // EXACT CODE FROM LIVEWIRE COMPONENT
    if ($row->document_type !== 'consultation_form') {
        echo "  Badge: N/A (not a consultation form)\n";
        continue;
    }

    // Check if assessment or plan is truly empty (null or empty string after trimming)
    $assessmentEmpty = is_null($row->assessment) || trim($row->assessment) === '';
    $planEmpty = is_null($row->plan) || trim($row->plan) === '';

    echo "  Assessment: " . ($assessmentEmpty ? 'EMPTY ❌' : 'HAS DATA ✅') . "\n";
    echo "  Plan: " . ($planEmpty ? 'EMPTY ❌' : 'HAS DATA ✅') . "\n";

    $needsAttention = $assessmentEmpty || $planEmpty;

    if ($needsAttention) {
        $missing = [];
        if ($assessmentEmpty) $missing[] = 'Assessment';
        if ($planEmpty) $missing[] = 'Plan';

        echo "  Badge: ⚠️ INCOMPLETE (Missing: " . implode(', ', $missing) . ")\n";
    } else {
        echo "  Badge: ✅ COMPLETE\n";
    }

    echo "\n";
}

echo str_repeat("-", 80) . "\n";
echo "\nExpected Results:\n";
echo "  ID 1: Complete ✅\n";
echo "  ID 2: Complete ✅\n";
echo "  ID 3: Complete ✅\n";
echo "  ID 4: Incomplete ❌\n";
