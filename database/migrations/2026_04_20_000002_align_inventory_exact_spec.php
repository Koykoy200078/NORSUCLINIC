<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->alignMedicineCategoryNaming();
        $this->alignMedicineBatchConstraints();
        $this->alignMedicineTransactionLedger();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->rollbackMedicineTransactionLedger();
        $this->rollbackMedicineBatchConstraints();
        $this->rollbackMedicineCategoryNaming();
    }

    private function alignMedicineCategoryNaming(): void
    {
        if (! Schema::hasTable('medicines')) {
            return;
        }

        if (! Schema::hasColumn('medicines', 'category')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('category')->nullable()->after('brand_name');
            });
        }

        if (Schema::hasColumn('medicines', 'category') && Schema::hasColumn('medicines', 'category_name')) {
            DB::statement(
                "UPDATE medicines
                 SET
                    category = COALESCE(NULLIF(category, ''), NULLIF(category_name, ''), 'Uncategorized'),
                    category_name = COALESCE(NULLIF(category_name, ''), NULLIF(category, ''), 'Uncategorized')"
            );
        } elseif (Schema::hasColumn('medicines', 'category')) {
            DB::statement(
                "UPDATE medicines
                 SET category = COALESCE(NULLIF(category, ''), 'Uncategorized')"
            );
        }

        try {
            DB::statement('ALTER TABLE medicines MODIFY COLUMN category VARCHAR(191) NOT NULL');
        } catch (\Throwable $e) {
            // Ignore engines that do not support this exact ALTER syntax.
        }
    }

    private function alignMedicineBatchConstraints(): void
    {
        if (! Schema::hasTable('medicine_batches')) {
            return;
        }

        DB::statement('UPDATE medicine_batches SET date_received = COALESCE(date_received, CURDATE())');
        DB::statement('UPDATE medicine_batches SET expiration_date = COALESCE(expiration_date, date_received, CURDATE())');
        DB::statement('UPDATE medicine_batches SET unit_cost = NULL WHERE unit_cost IS NOT NULL AND unit_cost > 999999.99');

        try {
            DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN expiration_date DATE NOT NULL');
            DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN date_received DATE NOT NULL');
            DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN unit_cost DECIMAL(8,2) NULL');
        } catch (\Throwable $e) {
            // Ignore engines that do not support this exact ALTER syntax.
        }
    }

    private function alignMedicineTransactionLedger(): void
    {
        if (! Schema::hasTable('medicine_transactions')) {
            return;
        }

        DB::statement(
            "UPDATE medicine_transactions
             SET transaction_type = CASE
                 WHEN transaction_type = 'stock_out' THEN 'dispense'
                 WHEN transaction_type = 'return' THEN 'disposal'
                 ELSE transaction_type
             END"
        );

        // Quantity in ledger should always store absolute moved amount.
        DB::statement('UPDATE medicine_transactions SET quantity = ABS(quantity)');

        try {
            DB::statement("ALTER TABLE medicine_transactions MODIFY COLUMN transaction_type ENUM('stock_in','dispense','adjustment','disposal') NOT NULL");
        } catch (\Throwable $e) {
            // Ignore engines that do not support this exact ALTER syntax.
        }
    }

    private function rollbackMedicineCategoryNaming(): void
    {
        if (! Schema::hasTable('medicines') || ! Schema::hasColumn('medicines', 'category')) {
            return;
        }

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    private function rollbackMedicineBatchConstraints(): void
    {
        if (! Schema::hasTable('medicine_batches')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN expiration_date DATE NULL');
            DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN date_received DATE NULL');
            DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN unit_cost DECIMAL(12,2) NULL');
        } catch (\Throwable $e) {
            // Ignore engines that do not support this exact ALTER syntax.
        }
    }

    private function rollbackMedicineTransactionLedger(): void
    {
        if (! Schema::hasTable('medicine_transactions')) {
            return;
        }

        DB::statement(
            "UPDATE medicine_transactions
             SET transaction_type = CASE
                 WHEN transaction_type = 'dispense' THEN 'stock_out'
                 WHEN transaction_type = 'disposal' THEN 'return'
                 ELSE transaction_type
             END"
        );

        try {
            DB::statement("ALTER TABLE medicine_transactions MODIFY COLUMN transaction_type ENUM('stock_in','stock_out','adjustment','return') NOT NULL");
        } catch (\Throwable $e) {
            // Ignore engines that do not support this exact ALTER syntax.
        }
    }
};
