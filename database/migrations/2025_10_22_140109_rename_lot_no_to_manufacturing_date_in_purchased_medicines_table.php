<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (
            Schema::hasTable('purchased_medicines') &&
            Schema::hasColumn('purchased_medicines', 'lot_no') &&
            ! Schema::hasColumn('purchased_medicines', 'manufacturing_date')
        ) {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                $table->renameColumn('lot_no', 'manufacturing_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('purchased_medicines') &&
            Schema::hasColumn('purchased_medicines', 'manufacturing_date') &&
            ! Schema::hasColumn('purchased_medicines', 'lot_no')
        ) {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                $table->renameColumn('manufacturing_date', 'lot_no');
            });
        }
    }
};
