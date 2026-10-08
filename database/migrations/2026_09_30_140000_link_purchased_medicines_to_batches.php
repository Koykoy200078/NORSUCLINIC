<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-04 / H-05: a stock-in line (purchased_medicines) had no link to the batch it created, so
 * editing or deleting a stock-in took stock from the wrong batch (FEFO across all batches) and
 * ignored medicine / dosage / expiry changes. Store the batch on the line.
 *
 * Existing lines are linked on a best-effort basis from the stock ledger: the line's stock-in
 * transaction for the same procurement, medicine, quantity and dosage. Anything ambiguous is left
 * NULL and keeps the previous (identity / FEFO based) handling.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchased_medicines') || Schema::hasColumn('purchased_medicines', 'batch_id')) {
            return;
        }

        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('medicine_id');
                $table->foreign('batch_id', 'purchased_medicines_batch_id_foreign')
                    ->references('id')->on('medicine_batches')->nullOnDelete();
            });
        });

        if (! Schema::hasTable('medicine_transactions')) {
            return;
        }

        $lines = DB::table('purchased_medicines')->useWritePdo()->whereNull('batch_id')->whereNotNull('medicine_id')->get();

        foreach ($lines as $line) {
            $candidates = DB::table('medicine_transactions as t')
                ->useWritePdo()
                ->join('medicine_batches as b', 'b.id', '=', 't.batch_id')
                ->where('t.transaction_type', 'stock_in')
                ->where('t.reference_id', $line->medicine_availabilities_id)
                ->whereIn('t.reference_type', ['App\\Models\\MedicineAvailability', 'App\\Models\\StockIn'])
                ->where('b.medicine_id', $line->medicine_id)
                ->where('t.quantity', $line->quantity)
                ->select('b.id', 'b.dosage')
                ->get()
                ->filter(function ($row) use ($line) {
                    return strcasecmp(trim((string) $row->dosage), trim((string) $line->dosage)) === 0;
                })
                ->pluck('id')
                ->unique();

            if ($candidates->count() === 1) {
                DB::table('purchased_medicines')->where('id', $line->id)->update(['batch_id' => $candidates->first()]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchased_medicines') && Schema::hasColumn('purchased_medicines', 'batch_id')) {
            LegacyMysqlMigration::withoutZeroDateChecks(function () {
                Schema::table('purchased_medicines', function (Blueprint $table) {
                    $table->dropForeign('purchased_medicines_batch_id_foreign');
                    $table->dropColumn('batch_id');
                });
            });
        }
    }
};
