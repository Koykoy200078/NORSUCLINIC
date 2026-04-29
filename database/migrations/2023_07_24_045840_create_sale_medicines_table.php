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
        Schema::create('sale_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_bill_id');
            $table->unsignedBigInteger('medicine_id');
            $table->integer('sale_quantity');
            $table->dateTime('expiry_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_medicines');
    }
};
