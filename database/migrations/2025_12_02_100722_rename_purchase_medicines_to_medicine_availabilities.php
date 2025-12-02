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
        Schema::rename('purchase_medicines', 'medicine_availabilities');

        // Rename the foreign key column in purchased_medicines table
        Schema::table('purchased_medicines', function (Blueprint $table) {
            $table->renameColumn('purchase_medicines_id', 'medicine_availabilities_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back the foreign key column in purchased_medicines table
        Schema::table('purchased_medicines', function (Blueprint $table) {
            $table->renameColumn('medicine_availabilities_id', 'purchase_medicines_id');
        });

        // Rename the table back
        Schema::rename('medicine_availabilities', 'purchase_medicines');
    }
};
