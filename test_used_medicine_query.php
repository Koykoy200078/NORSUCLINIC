<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ConsultationMedicine;

echo "==========================================\n";
echo "Testing Used Medicine Query\n";
echo "==========================================\n\n";

// Test 1: Check consultation_medicines count
echo "Test 1: Consultation Medicines Count\n";
$count = DB::table('consultation_medicines')->count();
echo "Count: $count\n\n";

// Test 2: Raw SQL query
echo "Test 2: Raw SQL Query\n";
$results = DB::select('
    SELECT 
        cm.id, 
        cm.quantity, 
        m.name as medicine_name, 
        rd.name as patient_name, 
        cm.used_for, 
        cm.created_at
    FROM consultation_medicines cm
    JOIN medicines m ON cm.medicine_id = m.id
    JOIN request_documents rd ON cm.request_document_id = rd.id
    LIMIT 5
');
echo "Results: " . count($results) . " rows\n";
foreach ($results as $row) {
    echo "  - ID: {$row->id}, Medicine: {$row->medicine_name}, Patient: {$row->patient_name}, Qty: {$row->quantity}\n";
}
echo "\n";

// Test 3: Eloquent query with joins
echo "Test 3: Eloquent Query with Joins\n";
$query = ConsultationMedicine::query()
    ->join('medicines', 'consultation_medicines.medicine_id', '=', 'medicines.id')
    ->join('request_documents', 'consultation_medicines.request_document_id', '=', 'request_documents.id')
    ->selectRaw('consultation_medicines.id')
    ->selectRaw('medicines.name as medicine_name')
    ->selectRaw('consultation_medicines.quantity')
    ->selectRaw('"Consultation" as source')
    ->selectRaw('request_documents.name as patient_name')
    ->selectRaw('COALESCE(consultation_medicines.used_for, "N/A") as used_for')
    ->selectRaw('consultation_medicines.created_at');

echo "SQL: " . $query->toSql() . "\n\n";

try {
    $results = $query->get();
    echo "Results: " . $results->count() . " rows\n";
    foreach ($results as $row) {
        echo "  - ID: {$row->id}, Medicine: {$row->medicine_name}, Patient: {$row->patient_name}, Source: {$row->source}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n==========================================\n";
echo "Tests Complete\n";
echo "==========================================\n";
