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
        // Rename brands table to generics
        Schema::rename('brands', 'generics');

        // Rename brand_id column to generic_id in medicines table
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->renameColumn('brand_id', 'generic_id');
        });

        // Re-add foreign key with new name
        Schema::table('medicines', function (Blueprint $table) {
            $table->foreign('generic_id')->references('id')->on('generics')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back to brands
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropForeign(['generic_id']);
            $table->renameColumn('generic_id', 'brand_id');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });

        Schema::rename('generics', 'brands');
    }
};
