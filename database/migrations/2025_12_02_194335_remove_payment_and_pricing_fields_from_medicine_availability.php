<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove payment and pricing fields from medicine_availabilities and purchased_medicines tables.
     * Medicine availability now only tracks stock quantities and dates, not financial transactions.
     */
    public function up(): void
    {
        // Remove payment-related columns from medicine_availabilities table
        Schema::table('medicine_availabilities', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['tax', 'total', 'net_amount', 'payment_type', 'discount', 'payment_note', 'note'] as $column) {
                if (Schema::hasColumn('medicine_availabilities', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Remove pricing columns from purchased_medicines table
        Schema::table('purchased_medicines', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['tax', 'amount'] as $column) {
                if (Schema::hasColumn('purchased_medicines', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore columns to medicine_availabilities table
        Schema::table('medicine_availabilities', function (Blueprint $table) {
            $table->float('tax')->default(0);
            $table->float('total')->default(0);
            $table->float('net_amount')->default(0);
            $table->integer('payment_type')->default(2);
            $table->float('discount')->default(0);
            $table->string('payment_note')->nullable();
            $table->string('note')->nullable();
        });

        // Restore columns to purchased_medicines table
        Schema::table('purchased_medicines', function (Blueprint $table) {
            $table->float('tax')->default(0);
            $table->float('amount')->default(0);
        });
    }
};
