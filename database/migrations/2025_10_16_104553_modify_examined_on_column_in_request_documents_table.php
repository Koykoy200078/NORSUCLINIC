<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Modify examined_on column to support multiple date formats:
     * - Single date: 2025-10-16
     * - Date range: 2025-10-12|2025-10-16|range
     * - Multiple dates: 2025-10-12,2025-10-14,2025-10-16|multiple
     */
    public function up(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            // Change examined_on from date to string to support date ranges and multiple dates
            $table->string('examined_on', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            // Revert back to date type
            $table->date('examined_on')->nullable()->change();
        });
    }
};
