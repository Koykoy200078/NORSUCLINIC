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
        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('minimum_stock_alert')->nullable()->after('available_quantity')
                ->comment('Minimum quantity threshold for stock alert');
            $table->decimal('stock_alert_percentage', 5, 2)->nullable()->after('minimum_stock_alert')
                ->comment('Percentage threshold for stock alert (e.g., 20 for 20%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['minimum_stock_alert', 'stock_alert_percentage']);
        });
    }
};
