<?php

// Quick script to check consultation form data
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RequestDocuments;

echo "=== Checking Consultation Forms ===\n\n";

$consultations = RequestDocuments::where('document_type', 'consultation_form')
    ->orderBy('id')
    ->get();

foreach ($consultations as $consultation) {
    echo "ID: {$consultation->id}\n";
    echo "Patient: {$consultation->name}\n";
    echo "Date: {$consultation->requested_at}\n";

    // Assessment check
    if (is_null($consultation->assessment)) {
        echo "Assessment: NULL\n";
    } elseif (trim($consultation->assessment) === '') {
        echo "Assessment: EMPTY STRING (length: " . strlen($consultation->assessment) . ")\n";
    } else {
        echo "Assessment: HAS DATA (length: " . strlen($consultation->assessment) . ")\n";
        echo "  Preview: " . substr($consultation->assessment, 0, 50) . "...\n";
    }

    // Plan check
    if (is_null($consultation->plan)) {
        echo "Plan: NULL\n";
    } elseif (trim($consultation->plan) === '') {
        echo "Plan: EMPTY STRING (length: " . strlen($consultation->plan) . ")\n";
    } else {
        echo "Plan: HAS DATA (length: " . strlen($consultation->plan) . ")\n";
        echo "  Preview: " . substr($consultation->plan, 0, 50) . "...\n";
    }

    // Status determination
    $assessmentEmpty = is_null($consultation->assessment) || trim($consultation->assessment) === '';
    $planEmpty = is_null($consultation->plan) || trim($consultation->plan) === '';
    $incomplete = $assessmentEmpty || $planEmpty;

    echo "Status: " . ($incomplete ? "INCOMPLETE" : "COMPLETE") . "\n";

    if ($incomplete) {
        $missing = [];
        if ($assessmentEmpty) $missing[] = 'Assessment';
        if ($planEmpty) $missing[] = 'Plan';
        echo "Missing: " . implode(', ', $missing) . "\n";
    }

    echo str_repeat("-", 70) . "\n\n";
}

echo "Total consultation forms: " . $consultations->count() . "\n";

$incompleteCount = RequestDocuments::where('document_type', 'consultation_form')
    ->where(function ($query) {
        $query->whereNull('assessment')
            ->orWhere('assessment', '')
            ->orWhereNull('plan')
            ->orWhere('plan', '');
    })
    ->count();

echo "Incomplete count (from query): {$incompleteCount}\n";
