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
        // Rename the table
        if (Schema::hasTable('purchase_medicines') && ! Schema::hasTable('medicine_availabilities')) {
            Schema::rename('purchase_medicines', 'medicine_availabilities');
        }

        // Rename the foreign key column in purchased_medicines table
        if (
            Schema::hasTable('purchased_medicines') &&
            Schema::hasColumn('purchased_medicines', 'purchase_medicines_id') &&
            ! Schema::hasColumn('purchased_medicines', 'medicine_availabilities_id')
        ) {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                $table->renameColumn('purchase_medicines_id', 'medicine_availabilities_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back the foreign key column in purchased_medicines table
        if (
            Schema::hasTable('purchased_medicines') &&
            Schema::hasColumn('purchased_medicines', 'medicine_availabilities_id') &&
            ! Schema::hasColumn('purchased_medicines', 'purchase_medicines_id')
        ) {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                $table->renameColumn('medicine_availabilities_id', 'purchase_medicines_id');
            });
        }

        // Rename the table back
        if (Schema::hasTable('medicine_availabilities') && ! Schema::hasTable('purchase_medicines')) {
            Schema::rename('medicine_availabilities', 'purchase_medicines');
        }
    }
};
