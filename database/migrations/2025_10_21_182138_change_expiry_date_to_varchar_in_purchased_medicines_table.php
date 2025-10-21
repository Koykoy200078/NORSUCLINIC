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
        Schema::table('purchased_medicines', function (Blueprint $table) {
            $table->string('expiry_date', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchased_medicines', function (Blueprint $table) {
            $table->dateTime('expiry_date')->nullable()->change();
        });
    }
};
