<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== DATABASE SCHEMA CHECK ===" . PHP_EOL;
echo PHP_EOL;

// Check if table exists
if (Schema::hasTable('purchased_medicines')) {
    echo "✓ Table 'purchased_medicines' EXISTS" . PHP_EOL;
    echo PHP_EOL;

    // Get all columns
    $columns = Schema::getColumnListing('purchased_medicines');
    echo "COLUMNS (" . count($columns) . "):" . PHP_EOL;
    foreach ($columns as $column) {
        echo "  - " . $column . PHP_EOL;
    }
    echo PHP_EOL;

    // Check specific columns
    echo "COLUMN CHECKS:" . PHP_EOL;
    echo "  - dosage column exists? " . (Schema::hasColumn('purchased_medicines', 'dosage') ? 'YES ✓' : 'NO ✗') . PHP_EOL;
    echo "  - expiry_date column exists? " . (Schema::hasColumn('purchased_medicines', 'expiry_date') ? 'YES ✓' : 'NO ✗') . PHP_EOL;
    echo "  - quantity column exists? " . (Schema::hasColumn('purchased_medicines', 'quantity') ? 'YES ✓' : 'NO ✗') . PHP_EOL;
    echo "  - medicine_id column exists? " . (Schema::hasColumn('purchased_medicines', 'medicine_id') ? 'YES ✓' : 'NO ✗') . PHP_EOL;
    echo PHP_EOL;

    // Get sample data
    echo "SAMPLE DATA:" . PHP_EOL;
    $sampleData = DB::table('purchased_medicines')->limit(3)->get();
    foreach ($sampleData as $index => $row) {
        echo "  Row " . ($index + 1) . ":" . PHP_EOL;
        echo "    ID: " . $row->id . PHP_EOL;
        echo "    Medicine ID: " . $row->medicine_id . PHP_EOL;
        echo "    Quantity: " . $row->quantity . PHP_EOL;
        echo "    Expiry Date: " . ($row->expiry_date ?? 'NULL') . PHP_EOL;
        if (property_exists($row, 'dosage')) {
            echo "    Dosage: " . ($row->dosage ?? 'NULL') . PHP_EOL;
        }
        echo PHP_EOL;
    }
} else {
    echo "✗ Table 'purchased_medicines' DOES NOT EXIST" . PHP_EOL;
}

echo "=== END ===" . PHP_EOL;
