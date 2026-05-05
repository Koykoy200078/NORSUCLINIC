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
            Schema::hasTable('medicine_bills') &&
            Schema::hasColumn('medicine_bills', 'bill_number') &&
            ! Schema::hasColumn('medicine_bills', 'history_number')
        ) {
            Schema::table('medicine_bills', function (Blueprint $table) {
                $table->renameColumn('bill_number', 'history_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('medicine_bills') &&
            Schema::hasColumn('medicine_bills', 'history_number') &&
            ! Schema::hasColumn('medicine_bills', 'bill_number')
        ) {
            Schema::table('medicine_bills', function (Blueprint $table) {
                $table->renameColumn('history_number', 'bill_number');
            });
        }
    }
};
