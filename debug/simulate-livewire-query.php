<?php

// Test what Livewire is actually getting
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RequestDocuments;

echo "=== SIMULATING LIVEWIRE QUERY ===\n\n";

// Simulate what Livewire does
$query = RequestDocuments::query();
$rows = $query->where('document_type', 'consultation_form')->get();

echo "Total rows fetched: " . $rows->count() . "\n\n";

foreach ($rows as $row) {
    echo str_repeat("=", 80) . "\n";
    echo "Row ID: {$row->id}\n";
    echo "Patient Name: {$row->name}\n";
    echo "Document Type: {$row->document_type}\n";

    // Debug the raw values
    echo "\n[RAW VALUES FROM DATABASE]\n";
    echo "  assessment (raw): ";
    var_dump($row->assessment);
    echo "  plan (raw): ";
    var_dump($row->plan);

    // Apply the EXACT logic from Livewire
    echo "\n[APPLYING LIVEWIRE LOGIC]\n";

    if ($row->document_type !== 'consultation_form') {
        echo "  Result: N/A (not a consultation form)\n";
        continue;
    }

    // EXACT CODE FROM LIVEWIRE
    $assessmentEmpty = is_null($row->assessment) || trim($row->assessment) === '';
    $planEmpty = is_null($row->plan) || trim($row->plan) === '';

    echo "  is_null(assessment): " . (is_null($row->assessment) ? 'TRUE' : 'FALSE') . "\n";
    echo "  trim(assessment) === '': " . ((trim($row->assessment) === '') ? 'TRUE' : 'FALSE') . "\n";
    echo "  assessmentEmpty: " . ($assessmentEmpty ? 'TRUE ❌' : 'FALSE ✅') . "\n";

    echo "  is_null(plan): " . (is_null($row->plan) ? 'TRUE' : 'FALSE') . "\n";
    echo "  trim(plan) === '': " . ((trim($row->plan) === '') ? 'TRUE' : 'FALSE') . "\n";
    echo "  planEmpty: " . ($planEmpty ? 'TRUE ❌' : 'FALSE ✅') . "\n";

    $needsAttention = $assessmentEmpty || $planEmpty;

    echo "\n  needsAttention: " . ($needsAttention ? 'TRUE' : 'FALSE') . "\n";

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

echo str_repeat("=", 80) . "\n";
