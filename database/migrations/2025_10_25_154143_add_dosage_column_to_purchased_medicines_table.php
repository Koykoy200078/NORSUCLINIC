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
        if (Schema::hasTable('purchased_medicines') && ! Schema::hasColumn('purchased_medicines', 'dosage')) {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                $table->string('dosage')->nullable()->after('medicine_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchased_medicines') && Schema::hasColumn('purchased_medicines', 'dosage')) {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                $table->dropColumn('dosage');
            });
        }
    }
};
