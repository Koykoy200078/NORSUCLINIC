<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-12: the history-number generator never detected collisions, so two dispense records could
 * share a number. Keep the oldest record's number, suffix later duplicates with their id, and
 * enforce uniqueness from now on.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medicine_bills') || ! Schema::hasColumn('medicine_bills', 'history_number')) {
            return;
        }

        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            $duplicates = DB::table('medicine_bills')
                ->useWritePdo()
                ->select('history_number')
                ->groupBy('history_number')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('history_number');

            foreach ($duplicates as $historyNumber) {
                $ids = DB::table('medicine_bills')->useWritePdo()->where('history_number', $historyNumber)->orderBy('id')->pluck('id');

                foreach ($ids->slice(1) as $id) {
                    DB::table('medicine_bills')->where('id', $id)->update([
                        'history_number' => $historyNumber . '-' . $id,
                    ]);
                }
            }

            Schema::table('medicine_bills', function ($table) {
                $table->dropIndex('idx_medicine_bills_history_number');
            });

            Schema::table('medicine_bills', function ($table) {
                $table->unique('history_number', 'uq_medicine_bills_history_number');
            });
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('medicine_bills')) {
            return;
        }

        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('medicine_bills', function ($table) {
                $table->dropUnique('uq_medicine_bills_history_number');
            });

            Schema::table('medicine_bills', function ($table) {
                $table->index('history_number', 'idx_medicine_bills_history_number');
            });
        });
    }
};
