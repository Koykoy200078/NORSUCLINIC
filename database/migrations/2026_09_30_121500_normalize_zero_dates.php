<?php

use App\Support\LegacySchemaUpgrade;
use Illuminate\Database\Migrations\Migration;

/**
 * CLINIC-DATA UPGRADE, step 3 (audit plan 1.3). The clinic's old server stored '0000-00-00' for "no date". Strict MySQL
 * will not rewrite a row holding one (every later ALTER or UPDATE of that row fails), so each zero or half-zero date
 * becomes NULL wherever the column allows NULL. It runs right after 2026_09_30_121000, which made the known columns
 * (sale_medicines.expiry_date, ...) nullable. A NOT NULL column that still holds one is listed, not changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $report = LegacySchemaUpgrade::normalizeZeroDates();

        foreach ($report['fixed'] as $where => $rows) {
            LegacySchemaUpgrade::announce("Zero date set to NULL in {$where} ({$rows} row(s)).");
        }

        foreach ($report['blocked'] as $where => $rows) {
            LegacySchemaUpgrade::announce(
                "{$where} still holds {$rows} zero date(s) and cannot be NULL: fix it by hand (php artisan db:integrity lists it).",
                true
            );
        }
    }

    public function down(): void
    {
        // Intentionally empty: the zero dates were placeholders for "no date"; NULL says the same thing.
    }
};
