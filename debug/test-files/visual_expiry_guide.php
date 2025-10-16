<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicine;
use Carbon\Carbon;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                        MEDICINE EXPIRY DISPLAY - VISUAL GUIDE                                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$medicines = Medicine::with(['brand'])->get();
$today = Carbon::now();

echo "┌────────────────────────────────────────────────────────────────────────────────────────────────────┐\n";
echo "│                                  EXAMPLE DISPLAYS                                                  │\n";
echo "└────────────────────────────────────────────────────────────────────────────────────────────────────┘\n";
echo "\n";

// Group medicines by status
$grouped = [
    'critical' => [],
    'warning' => [],
    'fresh' => [],
    'nodata' => []
];

foreach ($medicines as $medicine) {
    $qty = $medicine->available_quantity ?? 0;
    $expiryDate = $medicine->earliest_expiry_date;

    if ($qty > 0 && $expiryDate) {
        $days = $today->diffInDays(Carbon::parse($expiryDate), false);
        if ($days <= 7) {
            $grouped['critical'][] = $medicine;
        } elseif ($days <= 30) {
            $grouped['warning'][] = $medicine;
        } else {
            $grouped['fresh'][] = $medicine;
        }
    } else {
        $grouped['nodata'][] = $medicine;
    }
}

// Display Critical
if (count($grouped['critical']) > 0) {
    echo "🔴 CRITICAL - URGENT ACTION REQUIRED\n";
    echo str_repeat("─", 90) . "\n";
    foreach ($grouped['critical'] as $med) {
        $days = $today->diffInDays(Carbon::parse($med->earliest_expiry_date), false);
        $expiryStr = Carbon::parse($med->earliest_expiry_date)->format('M d, Y');
        echo "  📦 {$med->name}\n";
        echo "     Available: {$med->available_quantity} units\n";
        echo "     Display: [🔴 RED BADGE] {$expiryStr} ⚠️\n";
        echo "     Tooltip: " . ($days <= 0 ? "Expired!" : "Critical: {$days} day(s) left!") . "\n";
        echo "     Action:  ⚡ USE IMMEDIATELY or REMOVE FROM STOCK\n";
        echo "\n";
    }
} else {
    echo "🔴 CRITICAL - URGENT ACTION REQUIRED\n";
    echo str_repeat("─", 90) . "\n";
    echo "  ✓ No critical medicines found!\n\n";
}

// Display Warning
if (count($grouped['warning']) > 0) {
    echo "🟡 WARNING - EXPIRING WITHIN 30 DAYS\n";
    echo str_repeat("─", 90) . "\n";
    foreach ($grouped['warning'] as $med) {
        $days = $today->diffInDays(Carbon::parse($med->earliest_expiry_date), false);
        $expiryStr = Carbon::parse($med->earliest_expiry_date)->format('M d, Y');
        echo "  📦 {$med->name}\n";
        echo "     Available: {$med->available_quantity} units\n";
        echo "     Display: [🟡 YELLOW BADGE] {$expiryStr} ⚠️\n";
        echo "     Tooltip: Warning: {$days} day(s) until expiry\n";
        echo "     Action:  📋 Plan usage within the month\n";
        echo "\n";
    }
} else {
    echo "🟡 WARNING - EXPIRING WITHIN 30 DAYS\n";
    echo str_repeat("─", 90) . "\n";
    echo "  ✓ No medicines in warning period!\n\n";
}

// Display Fresh
if (count($grouped['fresh']) > 0) {
    echo "🟢 FRESH - SAFE TO USE\n";
    echo str_repeat("─", 90) . "\n";
    foreach ($grouped['fresh'] as $med) {
        $days = $today->diffInDays(Carbon::parse($med->earliest_expiry_date), false);
        $expiryStr = Carbon::parse($med->earliest_expiry_date)->format('M d, Y');
        echo "  📦 {$med->name}\n";
        echo "     Available: {$med->available_quantity} units\n";
        echo "     Display: [🟢 GREEN BADGE] {$expiryStr} ✓\n";
        echo "     Tooltip: Fresh: {$days} day(s) remaining\n";
        echo "     Action:  ✓ Normal usage and distribution\n";
        echo "\n";
    }
}

// Display No Data
if (count($grouped['nodata']) > 0) {
    echo "⚪ NO EXPIRY DATA - OUT OF STOCK OR NO DATE\n";
    echo str_repeat("─", 90) . "\n";
    $sample = array_slice($grouped['nodata'], 0, 3);
    foreach ($sample as $med) {
        $qty = $med->available_quantity ?? 0;
        echo "  📦 {$med->name}\n";
        echo "     Available: {$qty} units\n";
        echo "     Display: ℹ️ No expiry data (gray muted text)\n";
        echo "     Reason:  " . ($qty == 0 ? "Out of stock" : "No expiry date in database") . "\n";
        echo "     Action:  " . ($qty == 0 ? "Restock when needed" : "Add expiry date when available") . "\n";
        echo "\n";
    }
    if (count($grouped['nodata']) > 3) {
        echo "  ... and " . (count($grouped['nodata']) - 3) . " more\n\n";
    }
}

echo "\n";
echo "┌────────────────────────────────────────────────────────────────────────────────────────────────────┐\n";
echo "│                                  QUICK REFERENCE                                                   │\n";
echo "└────────────────────────────────────────────────────────────────────────────────────────────────────┘\n";
echo "\n";
echo "  Badge Color │ Days Left    │ Action Priority\n";
echo "  ────────────┼──────────────┼─────────────────────────────────────\n";
echo "  🔴 RED      │ ≤7 days      │ ⚡ URGENT - Use now or remove\n";
echo "  🟡 YELLOW   │ 8-30 days    │ 📋 PLAN - Schedule usage this month\n";
echo "  🟢 GREEN    │ >30 days     │ ✓ SAFE - Normal distribution\n";
echo "  ⚪ GRAY     │ N/A          │ ℹ️  INFO - No stock or no date\n";
echo "\n";

echo "┌────────────────────────────────────────────────────────────────────────────────────────────────────┐\n";
echo "│                                      SUMMARY                                                       │\n";
echo "└────────────────────────────────────────────────────────────────────────────────────────────────────┘\n";
echo "\n";
echo "  Total Medicines:     " . $medicines->count() . "\n";
echo "  🔴 Critical:         " . count($grouped['critical']) . " (Needs urgent attention)\n";
echo "  🟡 Warning:          " . count($grouped['warning']) . " (Plan usage soon)\n";
echo "  🟢 Fresh:            " . count($grouped['fresh']) . " (Good condition)\n";
echo "  ⚪ No Data:          " . count($grouped['nodata']) . " (Out of stock or no date)\n";
echo "\n";

if (count($grouped['critical']) > 0) {
    echo "  ⚠️  ACTION REQUIRED: You have " . count($grouped['critical']) . " critical medicine(s)!\n";
} else {
    echo "  ✓ All medicines are in good condition!\n";
}

echo "\n";
echo "══════════════════════════════════════════════════════════════════════════════════════════════════════\n";
echo " View your medicines at: http://127.0.0.1:8000/admin/medicines\n";
echo "══════════════════════════════════════════════════════════════════════════════════════════════════════\n";
echo "\n";
