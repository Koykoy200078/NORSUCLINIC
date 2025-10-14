<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UsedMedicineView;

echo "==========================================\n";
echo "Testing Used Medicines View\n";
echo "==========================================\n\n";

// Test 1: Direct DB query
echo "Test 1: Direct DB Query on View\n";
$directResults = DB::table('used_medicines_view')->get();
echo "Count: " . $directResults->count() . "\n";
foreach ($directResults as $row) {
    echo "  - ID: {$row->id}, Medicine: {$row->medicine_name}, Patient: {$row->patient_name}, Source: {$row->source}, Qty: {$row->quantity}\n";
}
echo "\n";

// Test 2: Using Eloquent Model
echo "Test 2: Using UsedMedicineView Model\n";
$modelResults = UsedMedicineView::query()->get();
echo "Count: " . $modelResults->count() . "\n";
foreach ($modelResults as $row) {
    echo "  - ID: {$row->id}, Medicine: {$row->medicine_name}, Patient: {$row->patient_name}, Source: {$row->source}, Qty: {$row->quantity}\n";
}
echo "\n";

// Test 3: With sorting and filtering
echo "Test 3: Testing Sorting (by created_at desc)\n";
$sortedResults = UsedMedicineView::query()
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();
echo "Count: " . $sortedResults->count() . "\n";
foreach ($sortedResults as $row) {
    echo "  - ID: {$row->id}, Medicine: {$row->medicine_name}, Date: {$row->created_at}, Source: {$row->source}\n";
}
echo "\n";

// Test 4: Search functionality
echo "Test 4: Testing Search (medicine_name LIKE '%ceti%')\n";
$searchResults = UsedMedicineView::query()
    ->where('medicine_name', 'like', '%ceti%')
    ->get();
echo "Count: " . $searchResults->count() . "\n";
foreach ($searchResults as $row) {
    echo "  - ID: {$row->id}, Medicine: {$row->medicine_name}, Source: {$row->source}\n";
}
echo "\n";

// Test 5: Count by source
echo "Test 5: Count by Source\n";
$consultationCount = UsedMedicineView::query()->where('source', 'Consultation')->count();
$saleCount = UsedMedicineView::query()->where('source', 'Medicine Bill')->count();
echo "Consultations: {$consultationCount}\n";
echo "Sales: {$saleCount}\n";
echo "Total: " . ($consultationCount + $saleCount) . "\n";
echo "\n";

echo "==========================================\n";
echo "Tests Complete\n";
echo "==========================================\n";
