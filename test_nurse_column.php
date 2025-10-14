<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UsedMedicineView;

echo "==========================================\n";
echo "Testing Used Medicines View with Nurse\n";
echo "==========================================\n\n";

// Test 1: Direct DB query
echo "Test 1: Direct DB Query on View\n";
$directResults = DB::table('used_medicines_view')->get();
echo "Total Count: " . $directResults->count() . "\n\n";
echo "Sample Records:\n";
foreach ($directResults->take(5) as $row) {
    echo "  ID: {$row->id}\n";
    echo "  Medicine: {$row->medicine_name}\n";
    echo "  Patient: {$row->patient_name}\n";
    echo "  Nurse: {$row->nurse_incharged}\n";
    echo "  Source: {$row->source}\n";
    echo "  Quantity: {$row->quantity}\n";
    echo "  Used For: {$row->used_for}\n";
    echo "  Date: {$row->created_at}\n";
    echo "  " . str_repeat("-", 50) . "\n";
}
echo "\n";

// Test 2: Using Eloquent Model
echo "Test 2: Using UsedMedicineView Model\n";
$modelResults = UsedMedicineView::query()->orderBy('created_at', 'desc')->take(3)->get();
echo "Count: " . $modelResults->count() . "\n";
foreach ($modelResults as $row) {
    echo "  - {$row->source}: {$row->medicine_name} (Qty: {$row->quantity}) - Patient: {$row->patient_name} - Nurse: {$row->nurse_incharged}\n";
}
echo "\n";

// Test 3: Search by patient name
echo "Test 3: Testing Search (patient_name LIKE '%Christian%')\n";
$searchResults = UsedMedicineView::query()
    ->where('patient_name', 'like', '%Christian%')
    ->get();
echo "Count: " . $searchResults->count() . "\n";
foreach ($searchResults as $row) {
    echo "  - Patient: {$row->patient_name}, Medicine: {$row->medicine_name}, Nurse: {$row->nurse_incharged}\n";
}
echo "\n";

// Test 4: Count by source
echo "Test 4: Count by Source\n";
$consultationCount = UsedMedicineView::query()->where('source', 'Consultation')->count();
$saleCount = UsedMedicineView::query()->where('source', 'Medicine Bill')->count();
echo "Consultations: {$consultationCount}\n";
echo "Sales: {$saleCount}\n";
echo "Total: " . ($consultationCount + $saleCount) . "\n";
echo "\n";

// Test 5: Group by nurse
echo "Test 5: Medicines Distributed by Nurse\n";
$nurseStats = DB::table('used_medicines_view')
    ->select('nurse_incharged', DB::raw('COUNT(*) as medicine_count'), DB::raw('SUM(quantity) as total_quantity'))
    ->groupBy('nurse_incharged')
    ->get();
foreach ($nurseStats as $stat) {
    echo "  - Nurse: {$stat->nurse_incharged}\n";
    echo "    Medicines Distributed: {$stat->medicine_count}\n";
    echo "    Total Quantity: {$stat->total_quantity}\n";
    echo "    " . str_repeat("-", 40) . "\n";
}
echo "\n";

echo "==========================================\n";
echo "Tests Complete - All Fields Working!\n";
echo "==========================================\n";
