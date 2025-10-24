<?php

// Test the new approach with fresh database reads
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RequestDocuments;

echo "=== TESTING NEW APPROACH WITH FRESH DB READS ===\n\n";

$rows = RequestDocuments::where('document_type', 'consultation_form')->get();

foreach ($rows as $row) {
    echo str_repeat("=", 80) . "\n";
    echo "ID: {$row->id} | Patient: {$row->name}\n";

    // NEW APPROACH: Fresh read from database
    $record = RequestDocuments::find($row->id);

    if (!$record) {
        echo "  Status: Unknown (record not found)\n\n";
        continue;
    }

    // Check if fields are truly empty using !empty(trim())
    $hasAssessment = !empty(trim((string)$record->assessment));
    $hasPlan = !empty(trim((string)$record->plan));

    echo "  Assessment value: " . var_export($record->assessment, true) . "\n";
    echo "  Assessment length: " . strlen($record->assessment) . "\n";
    echo "  Assessment trimmed: '" . trim((string)$record->assessment) . "'\n";
    echo "  hasAssessment (using !empty): " . ($hasAssessment ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";

    echo "  Plan value: " . var_export($record->plan, true) . "\n";
    echo "  Plan length: " . strlen($record->plan) . "\n";
    echo "  Plan trimmed: '" . trim((string)$record->plan) . "'\n";
    echo "  hasPlan (using !empty): " . ($hasPlan ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";

    // Both fields have data = Complete
    if ($hasAssessment && $hasPlan) {
        echo "  BADGE: ✅ COMPLETE\n";
    } else {
        $missing = [];
        if (!$hasAssessment) $missing[] = 'Assessment';
        if (!$hasPlan) $missing[] = 'Plan';
        echo "  BADGE: ⚠️ INCOMPLETE (Missing: " . implode(', ', $missing) . ")\n";
    }

    echo "\n";
}

echo str_repeat("=", 80) . "\n";
