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
        if (Schema::hasTable('sale_medicines') && ! Schema::hasColumn('sale_medicines', 'dosage')) {
            Schema::table('sale_medicines', function (Blueprint $table) {
                $table->string('dosage', 100)->nullable()->after('medicine_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_medicines') && Schema::hasColumn('sale_medicines', 'dosage')) {
            Schema::table('sale_medicines', function (Blueprint $table) {
                $table->dropColumn('dosage');
            });
        }
    }
};
