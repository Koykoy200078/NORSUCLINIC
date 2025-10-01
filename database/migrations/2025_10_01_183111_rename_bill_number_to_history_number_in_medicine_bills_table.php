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
        Schema::table('medicine_bills', function (Blueprint $table) {
            $table->renameColumn('bill_number', 'history_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_bills', function (Blueprint $table) {
            $table->renameColumn('history_number', 'bill_number');
        });
    }
};
