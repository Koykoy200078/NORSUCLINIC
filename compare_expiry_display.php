<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicine;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    MEDICINE EXPIRY DISPLAY - BEFORE vs AFTER                                        ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$zeroQtyMedicines = Medicine::where('available_quantity', 0)->get();

if ($zeroQtyMedicines->count() > 0) {
    echo "MEDICINES WITH ZERO QUANTITY:\n";
    echo str_repeat("=", 100) . "\n\n";

    foreach ($zeroQtyMedicines as $medicine) {
        $expiryDate = $medicine->earliest_expiry_date;

        echo "Medicine: " . $medicine->name . "\n";
        echo str_repeat("-", 100) . "\n";
        echo "Available Quantity: 0\n";
        echo "Database Expiry Date: " . ($expiryDate ? \Carbon\Carbon::parse($expiryDate)->format('M d, Y') : 'N/A') . "\n";
        echo "\n";

        // BEFORE (old behavior)
        echo "❌ BEFORE:\n";
        if ($expiryDate) {
            echo "   Display: Badge with date → " . \Carbon\Carbon::parse($expiryDate)->format('M d, Y') . "\n";
            echo "   Issue: Shows expiry for medicine that's not in stock\n";
        } else {
            echo "   Display: No expiry data\n";
        }
        echo "\n";

        // AFTER (new behavior)
        echo "✅ AFTER:\n";
        echo "   Display: No expiry data\n";
        echo "   Benefit: Cleaner view, focuses only on available medicines\n";
        echo "\n";
        echo str_repeat("=", 100) . "\n\n";
    }
} else {
    echo "No medicines with zero quantity found.\n\n";
}

// Show medicines with stock
$inStockMedicines = Medicine::where('available_quantity', '>', 0)->get();

if ($inStockMedicines->count() > 0) {
    echo "\nMEDICINES WITH STOCK (Unchanged Behavior):\n";
    echo str_repeat("=", 100) . "\n\n";

    foreach ($inStockMedicines as $medicine) {
        $expiryDate = $medicine->earliest_expiry_date;

        echo sprintf(
            "%-30s | Qty: %-5s | ",
            substr($medicine->name, 0, 30),
            $medicine->available_quantity
        );

        if ($expiryDate) {
            $expiryCarbon = \Carbon\Carbon::parse($expiryDate);
            $today = \Carbon\Carbon::now();
            $oneMonthFromNow = \Carbon\Carbon::now()->addMonth();

            if ($expiryCarbon->lt($today)) {
                echo "🔴 EXPIRED: " . $expiryCarbon->format('M d, Y');
            } elseif ($expiryCarbon->lte($oneMonthFromNow)) {
                echo "🟡 EXPIRING: " . $expiryCarbon->format('M d, Y');
            } else {
                echo "🟢 FRESH: " . $expiryCarbon->format('M d, Y');
            }
        } else {
            echo "⚪ No expiry data";
        }
        echo "\n";
    }
    echo "\n";
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                        KEY CHANGES                                                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "✅ Medicines with 0 quantity → Always show 'No expiry data'\n";
echo "✅ Medicines with stock > 0 → Show expiry date (if available)\n";
echo "✅ Reduces confusion about expired medicines that aren't in stock\n";
echo "✅ Focuses attention on medicines that actually need monitoring\n";
echo "\n";

echo "══════════════════════════════════════════════════════════════════════════════════════════════════════\n";
echo " View the changes at: http://127.0.0.1:8000/admin/medicines\n";
echo "══════════════════════════════════════════════════════════════════════════════════════════════════════\n";
echo "\n";
