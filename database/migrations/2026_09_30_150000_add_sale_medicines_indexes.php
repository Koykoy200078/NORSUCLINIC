<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * M-11: sale_medicines (dispense items) had no index on its foreign-key style columns although the
 * dispensing tables sum it once per row, so every list page scanned the whole table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sale_medicines')) {
            return;
        }

        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('sale_medicines', function (Blueprint $table) {
                if (! $this->hasIndex('sale_medicines', 'idx_sale_medicines_bill')) {
                    $table->index('medicine_bill_id', 'idx_sale_medicines_bill');
                }
                if (! $this->hasIndex('sale_medicines', 'idx_sale_medicines_medicine')) {
                    $table->index('medicine_id', 'idx_sale_medicines_medicine');
                }
            });
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sale_medicines')) {
            return;
        }

        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('sale_medicines', function (Blueprint $table) {
                if ($this->hasIndex('sale_medicines', 'idx_sale_medicines_bill')) {
                    $table->dropIndex('idx_sale_medicines_bill');
                }
                if ($this->hasIndex('sale_medicines', 'idx_sale_medicines_medicine')) {
                    $table->dropIndex('idx_sale_medicines_medicine');
                }
            });
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return false;
        }

        return DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$table, $index]
        )->c > 0;
    }
};
