<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Medicine;
use App\Models\PurchasedMedicine;
use Illuminate\Support\Facades\DB;

echo "=== TESTING MEDICINE MODAL DATA ===" . PHP_EOL;
echo PHP_EOL;

// Get first medicine that has purchased medicines
$medicineId = DB::table('purchased_medicines')->where('quantity', '>', 0)->value('medicine_id');
$medicine = Medicine::find($medicineId);

if (!$medicine) {
    echo "✗ No medicines found with purchased medicines" . PHP_EOL;
    exit;
}

echo "Testing Medicine: " . $medicine->name . " (ID: " . $medicine->id . ")" . PHP_EOL;
echo PHP_EOL;

// Simulate the controller query
$purchasedMedicines = PurchasedMedicine::where('medicine_id', $medicine->id)
    ->where('quantity', '>', 0)
    ->select(
        'dosage',
        DB::raw('SUM(quantity) as total_quantity'),
        DB::raw('MIN(expiry_date) as earliest_expiry')
    )
    ->groupBy('dosage')
    ->orderBy('dosage')
    ->get();

echo "Query Result (" . $purchasedMedicines->count() . " rows):" . PHP_EOL;
echo "─────────────────────────────────────────────────────────" . PHP_EOL;

foreach ($purchasedMedicines as $item) {
    echo "Dosage: " . ($item->dosage ?? 'N/A') . PHP_EOL;
    echo "  Total Quantity: " . $item->total_quantity . PHP_EOL;
    echo "  Earliest Expiry (raw): " . ($item->earliest_expiry ?? 'NULL') . PHP_EOL;

    // Try to parse the date
    if ($item->earliest_expiry) {
        try {
            $expiry = \Carbon\Carbon::parse($item->earliest_expiry);
            $now = \Carbon\Carbon::now();
            $remainingDays = (int) $now->diffInDays($expiry, false);
            $formatted = $expiry->format('M d, Y');

            echo "  Formatted Expiry: " . $formatted . PHP_EOL;
            echo "  Remaining Days: " . $remainingDays . PHP_EOL;

            if ($remainingDays < 0) {
                echo "  Status: EXPIRED (" . abs($remainingDays) . " days ago) 🔴" . PHP_EOL;
            } elseif ($remainingDays === 0) {
                echo "  Status: EXPIRES TODAY 🔴" . PHP_EOL;
            } elseif ($remainingDays <= 30) {
                echo "  Status: CRITICAL (≤30 days) 🟡" . PHP_EOL;
            } elseif ($remainingDays <= 90) {
                echo "  Status: WARNING (≤90 days) 🔵" . PHP_EOL;
            } else {
                echo "  Status: GOOD (>90 days) 🟢" . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo "  Parse Error: " . $e->getMessage() . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

echo "=== TEST COMPLETE ===" . PHP_EOL;
