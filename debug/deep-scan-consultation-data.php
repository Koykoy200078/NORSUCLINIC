<?php

// Deep scan script to check consultation form data with detailed analysis
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RequestDocuments;

echo "=== DEEP SCAN: Consultation Forms Data Analysis ===\n\n";

$consultations = RequestDocuments::where('document_type', 'consultation_form')
    ->orderBy('id')
    ->get();

foreach ($consultations as $consultation) {
    echo str_repeat("=", 80) . "\n";
    echo "ID: {$consultation->id}\n";
    echo "Patient: {$consultation->name}\n";
    echo "Date: {$consultation->requested_at}\n";
    echo str_repeat("-", 80) . "\n";

    // Deep analysis of Assessment field
    echo "\n[ASSESSMENT FIELD ANALYSIS]\n";
    if (is_null($consultation->assessment)) {
        echo "  Type: NULL\n";
        echo "  Status: EMPTY ❌\n";
    } else {
        echo "  Type: " . gettype($consultation->assessment) . "\n";
        echo "  Raw Length: " . strlen($consultation->assessment) . " characters\n";
        echo "  Trimmed Length: " . strlen(trim($consultation->assessment)) . " characters\n";
        echo "  Is Empty String: " . (($consultation->assessment === '') ? 'YES' : 'NO') . "\n";
        echo "  Is Whitespace Only: " . ((trim($consultation->assessment) === '') ? 'YES' : 'NO') . "\n";

        if (trim($consultation->assessment) !== '') {
            echo "  Status: HAS DATA ✅\n";
            echo "  Content Preview: \"" . substr($consultation->assessment, 0, 100) . "\"\n";
            echo "  First 20 chars (hex): " . bin2hex(substr($consultation->assessment, 0, 20)) . "\n";
        } else {
            echo "  Status: EMPTY ❌\n";
        }
    }

    // Deep analysis of Plan field
    echo "\n[PLAN FIELD ANALYSIS]\n";
    if (is_null($consultation->plan)) {
        echo "  Type: NULL\n";
        echo "  Status: EMPTY ❌\n";
    } else {
        echo "  Type: " . gettype($consultation->plan) . "\n";
        echo "  Raw Length: " . strlen($consultation->plan) . " characters\n";
        echo "  Trimmed Length: " . strlen(trim($consultation->plan)) . " characters\n";
        echo "  Is Empty String: " . (($consultation->plan === '') ? 'YES' : 'NO') . "\n";
        echo "  Is Whitespace Only: " . ((trim($consultation->plan) === '') ? 'YES' : 'NO') . "\n";

        if (trim($consultation->plan) !== '') {
            echo "  Status: HAS DATA ✅\n";
            echo "  Content Preview: \"" . substr($consultation->plan, 0, 100) . "\"\n";
            echo "  First 20 chars (hex): " . bin2hex(substr($consultation->plan, 0, 20)) . "\n";
        } else {
            echo "  Status: EMPTY ❌\n";
        }
    }

    // Overall Status Determination
    echo "\n[OVERALL STATUS]\n";
    $assessmentEmpty = is_null($consultation->assessment) || trim($consultation->assessment) === '';
    $planEmpty = is_null($consultation->plan) || trim($consultation->plan) === '';

    echo "  Assessment Empty: " . ($assessmentEmpty ? 'YES ❌' : 'NO ✅') . "\n";
    echo "  Plan Empty: " . ($planEmpty ? 'YES ❌' : 'NO ✅') . "\n";

    if (!$assessmentEmpty && !$planEmpty) {
        echo "  FINAL STATUS: ✅ COMPLETE (Both fields have data)\n";
    } elseif ($assessmentEmpty && $planEmpty) {
        echo "  FINAL STATUS: ❌ INCOMPLETE (Both fields are empty)\n";
    } else {
        $missing = [];
        if ($assessmentEmpty) $missing[] = 'Assessment';
        if ($planEmpty) $missing[] = 'Plan';
        echo "  FINAL STATUS: ⚠️ INCOMPLETE (Missing: " . implode(', ', $missing) . ")\n";
    }

    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "\n[SUMMARY]\n";
echo "Total consultation forms: " . $consultations->count() . "\n";

// Count complete vs incomplete
$complete = 0;
$incomplete = 0;

foreach ($consultations as $consultation) {
    $assessmentEmpty = is_null($consultation->assessment) || trim($consultation->assessment) === '';
    $planEmpty = is_null($consultation->plan) || trim($consultation->plan) === '';

    if (!$assessmentEmpty && !$planEmpty) {
        $complete++;
    } else {
        $incomplete++;
    }
}

echo "Complete forms: {$complete} ✅\n";
echo "Incomplete forms: {$incomplete} ❌\n";

// Verify with database query
$incompleteCountFromQuery = RequestDocuments::where('document_type', 'consultation_form')
    ->where(function ($query) {
        $query->whereNull('assessment')
            ->orWhere('assessment', '')
            ->orWhereNull('plan')
            ->orWhere('plan', '');
    })
    ->count();

echo "\nDatabase Query Count (incomplete): {$incompleteCountFromQuery}\n";

echo "\n" . str_repeat("=", 80) . "\n";
