<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicine;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    MEDICINE EXPIRATION DISPLAY TEST                                           ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$medicines = Medicine::with(['brand', 'category'])->get();

echo "Testing Medicine Expiration Display Logic:\n";
echo str_repeat("-", 100) . "\n";
echo sprintf(
    "%-5s | %-30s | %-10s | %-20s | %-25s\n",
    "ID",
    "Medicine Name",
    "Avail Qty",
    "Expiry Date",
    "Display"
);
echo str_repeat("-", 100) . "\n";

foreach ($medicines as $medicine) {
    $availableQty = $medicine->available_quantity ?? 0;
    $expiryDate = $medicine->earliest_expiry_date;

    // Apply the same logic as the blade file
    if ($availableQty == 0) {
        $display = "No expiry data (Qty = 0)";
    } elseif ($expiryDate) {
        $formattedDate = \Carbon\Carbon::parse($expiryDate)->format('M d, Y');
        $expiryCarbon = \Carbon\Carbon::parse($expiryDate);
        $today = \Carbon\Carbon::now();
        $oneMonthFromNow = \Carbon\Carbon::now()->addMonth();

        if ($expiryCarbon->lt($today)) {
            $display = "⚠️  EXPIRED: {$formattedDate}";
        } elseif ($expiryCarbon->lte($oneMonthFromNow)) {
            $display = "⚠️  EXPIRING: {$formattedDate}";
        } else {
            $display = "✅ Fresh: {$formattedDate}";
        }
    } else {
        $display = "No expiry data";
    }

    $expiryDateStr = $expiryDate ? \Carbon\Carbon::parse($expiryDate)->format('Y-m-d') : 'N/A';

    echo sprintf(
        "%-5s | %-30s | %-10s | %-20s | %-25s\n",
        $medicine->id,
        substr($medicine->name, 0, 30),
        $availableQty,
        $expiryDateStr,
        substr($display, 0, 25)
    );
}

echo str_repeat("-", 100) . "\n";
echo "\n";

// Count medicines by status
$totalMedicines = $medicines->count();
$zeroQuantity = $medicines->where('available_quantity', 0)->count();
$withExpiry = $medicines->filter(function ($m) {
    return $m->available_quantity > 0 && $m->earliest_expiry_date != null;
})->count();
$noExpiry = $medicines->filter(function ($m) {
    return $m->available_quantity > 0 && $m->earliest_expiry_date == null;
})->count();

echo "╔═══════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                    STATISTICS                                                 ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Total Medicines: {$totalMedicines}\n";
echo "  - Zero Quantity (will show 'No expiry data'): {$zeroQuantity}\n";
echo "  - With Stock & Expiry Date: {$withExpiry}\n";
echo "  - With Stock but No Expiry: {$noExpiry}\n";
echo "\n";

echo "╔═══════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                      BEHAVIOR                                                 ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "When available_quantity = 0:\n";
echo "  ✓ Display: 'No expiry data' (regardless of actual expiry date)\n";
echo "  ✓ Icon: Info icon with muted text\n";
echo "  ✓ Reason: Out of stock medicines don't need expiry tracking\n";
echo "\n";
echo "When available_quantity > 0:\n";
echo "  ✓ If expiry date exists: Show date with color-coded badge\n";
echo "  ✓ If no expiry date: Show 'No expiry data'\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════════════════════════════════════\n";
echo " View updated at: http://127.0.0.1:8000/admin/medicines\n";
echo "═══════════════════════════════════════════════════════════════════════════════════════════════\n";
echo "\n";
