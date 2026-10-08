<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. Lab requests are clinical records too: deleting one (only pending / cancelled ones can be deleted) now sets
 *    `deleted_at` instead of removing the row, like consultations and certificates. (R3-M6)
 * 2. Stock-in numbers get a unique index so two simultaneous stock-ins can never share a number (the number is
 *    drawn at random and checked before saving, which leaves a tiny race; the index makes it impossible and the
 *    save retries with a new number). Lab request numbers already have one. (pass-1 L-05)
 *    If an existing copy already holds duplicate numbers the index is skipped here instead of failing the whole
 *    migration - fix the duplicates and add it by hand.
 */
return new class extends Migration
{
    private const INDEX = 'medicine_availabilities_availability_no_unique';

    public function up(): void
    {
        if (! Schema::hasColumn('lab_requests', 'deleted_at')) {
            LegacyMysqlMigration::withoutZeroDateChecks(function () {
                Schema::table('lab_requests', function (Blueprint $table) {
                    $table->softDeletes();
                    $table->index('deleted_at', 'lab_requests_deleted_at_index');
                });
            });
        }

        if (Schema::hasTable('medicine_availabilities') && Schema::hasColumn('medicine_availabilities', 'availability_no')) {
            $duplicates = DB::table('medicine_availabilities')
                ->select('availability_no')
                ->whereNotNull('availability_no')
                ->groupBy('availability_no')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            $hasIndex = collect(DB::select('SHOW INDEX FROM medicine_availabilities'))
                ->contains(fn ($index) => $index->Column_name === 'availability_no' && (int) $index->Non_unique === 0);

            if ($duplicates === 0 && ! $hasIndex) {
                LegacyMysqlMigration::withoutZeroDateChecks(function () {
                    Schema::table('medicine_availabilities', function (Blueprint $table) {
                        $table->unique('availability_no', self::INDEX);
                    });
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lab_requests', 'deleted_at')) {
            $deleted = DB::table('lab_requests')->whereNotNull('deleted_at')->count();
            if ($deleted > 0) {
                throw new RuntimeException("{$deleted} deleted lab request(s) would become visible again. Restore or purge them first.");
            }

            LegacyMysqlMigration::withoutZeroDateChecks(function () {
                Schema::table('lab_requests', function (Blueprint $table) {
                    $table->dropIndex('lab_requests_deleted_at_index');
                    $table->dropSoftDeletes();
                });
            });
        }

        $hasIndex = collect(DB::select('SHOW INDEX FROM medicine_availabilities'))->contains(fn ($index) => $index->Key_name === self::INDEX);
        if ($hasIndex) {
            LegacyMysqlMigration::withoutZeroDateChecks(function () {
                Schema::table('medicine_availabilities', function (Blueprint $table) {
                    $table->dropUnique(self::INDEX);
                });
            });
        }
    }
};
