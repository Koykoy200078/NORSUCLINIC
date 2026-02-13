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
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191); // Limited to 191 chars for MySQL utf8mb4 composite index compatibility
            $table->unsignedBigInteger('city_id');
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->index(['city_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
