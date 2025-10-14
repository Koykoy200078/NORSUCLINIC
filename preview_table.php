<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              USED MEDICINE TABLE - PREVIEW                                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$results = DB::table('used_medicines_view')->orderBy('created_at', 'desc')->get();

// Header
echo sprintf(
    "%-5s | %-25s | %-5s | %-15s | %-25s | %-20s | %-10s\n",
    "ID",
    "Medicine",
    "Qty",
    "Source",
    "Patient",
    "Nurse In Charge",
    "Date"
);
echo str_repeat("-", 130) . "\n";

// Data rows
foreach ($results as $row) {
    $date = date('Y-m-d', strtotime($row->created_at));
    $source = $row->source === 'Consultation' ? '🏥 Consult' : '💊 Sale';

    echo sprintf(
        "%-5s | %-25s | %-5s | %-15s | %-25s | %-20s | %-10s\n",
        substr($row->id, 0, 5),
        substr($row->medicine_name, 0, 25),
        $row->quantity,
        $source,
        substr($row->patient_name, 0, 25),
        substr($row->nurse_incharged, 0, 20),
        $date
    );
}

echo str_repeat("-", 130) . "\n";
echo "Total Records: " . $results->count() . "\n\n";

echo "╔═══════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              FEATURES AVAILABLE                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "  ✓ Sortable by: Medicine, Quantity, Source, Patient, Nurse, Date\n";
echo "  ✓ Searchable by: Medicine name, Patient name, Nurse name\n";
echo "  ✓ Filterable by: Source (Consultation/Sale)\n";
echo "  ✓ Badge indicators: Blue (Consultation), Green (Sale)\n";
echo "  ✓ Shows 'used_for' details for consultations (plan/nursing)\n";
echo "\n";

echo "╔═══════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              STATISTICS                                                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Consultations by nurse
$nurseStats = DB::table('used_medicines_view')
    ->where('source', 'Consultation')
    ->select(
        'nurse_incharged',
        DB::raw('COUNT(*) as consultations'),
        DB::raw('SUM(quantity) as total_medicines')
    )
    ->groupBy('nurse_incharged')
    ->get();

echo "Medicines Distributed by Nurse:\n";
echo str_repeat("-", 70) . "\n";
foreach ($nurseStats as $stat) {
    echo sprintf(
        "  %-30s | Consultations: %3d | Medicines: %3d\n",
        $stat->nurse_incharged,
        $stat->consultations,
        $stat->total_medicines
    );
}
echo "\n";

// Patient statistics
$patientStats = DB::table('used_medicines_view')
    ->select(
        'patient_name',
        DB::raw('COUNT(*) as medicine_count'),
        DB::raw('SUM(quantity) as total_quantity')
    )
    ->groupBy('patient_name')
    ->orderBy('total_quantity', 'desc')
    ->get();

echo "Medicines Used by Patient:\n";
echo str_repeat("-", 70) . "\n";
foreach ($patientStats as $stat) {
    echo sprintf(
        "  %-35s | Records: %3d | Total Qty: %3d\n",
        substr($stat->patient_name, 0, 35),
        $stat->medicine_count,
        $stat->total_quantity
    );
}
echo "\n";

echo "═══════════════════════════════════════════════════════════════════════════════════════════════════════\n";
echo " Access the table at: http://127.0.0.1:8000/admin/used-medicine\n";
echo "═══════════════════════════════════════════════════════════════════════════════════════════════════════\n";
echo "\n";
