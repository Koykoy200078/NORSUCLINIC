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
        Schema::table('medicine_availabilities', function (Blueprint $table) {
            $table->renameColumn('purchase_no', 'availability_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_availabilities', function (Blueprint $table) {
            $table->renameColumn('availability_no', 'purchase_no');
        });
    }
};
