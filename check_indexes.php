<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Appointments Table Indexes ===\n\n";

$indexes = DB::select("SHOW INDEX FROM appointments WHERE Key_name LIKE 'idx_appointments_%'");

foreach ($indexes as $index) {
    printf(
        "Index: %-40s | Column: %-20s | Sub_part: %-5s | Seq: %d\n",
        $index->Key_name,
        $index->Column_name,
        $index->Sub_part ?? 'NULL',
        $index->Seq_in_index
    );
}

echo "\n=== Summary ===\n";
echo "Total indexes found: " . count($indexes) . "\n\n";
