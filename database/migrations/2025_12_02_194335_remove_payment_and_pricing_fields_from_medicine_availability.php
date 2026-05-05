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
        if (Schema::hasTable('medicine_availabilities')) {
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
        }

        // Remove pricing columns from purchased_medicines table
        if (Schema::hasTable('purchased_medicines')) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore columns to medicine_availabilities table
        if (Schema::hasTable('medicine_availabilities')) {
            Schema::table('medicine_availabilities', function (Blueprint $table) {
                if (! Schema::hasColumn('medicine_availabilities', 'tax')) {
                    $table->float('tax')->default(0);
                }
                if (! Schema::hasColumn('medicine_availabilities', 'total')) {
                    $table->float('total')->default(0);
                }
                if (! Schema::hasColumn('medicine_availabilities', 'net_amount')) {
                    $table->float('net_amount')->default(0);
                }
                if (! Schema::hasColumn('medicine_availabilities', 'payment_type')) {
                    $table->integer('payment_type')->default(2);
                }
                if (! Schema::hasColumn('medicine_availabilities', 'discount')) {
                    $table->float('discount')->default(0);
                }
                if (! Schema::hasColumn('medicine_availabilities', 'payment_note')) {
                    $table->string('payment_note')->nullable();
                }
                if (! Schema::hasColumn('medicine_availabilities', 'note')) {
                    $table->string('note')->nullable();
                }
            });
        }

        // Restore columns to purchased_medicines table
        if (Schema::hasTable('purchased_medicines')) {
            Schema::table('purchased_medicines', function (Blueprint $table) {
                if (! Schema::hasColumn('purchased_medicines', 'tax')) {
                    $table->float('tax')->default(0);
                }
                if (! Schema::hasColumn('purchased_medicines', 'amount')) {
                    $table->float('amount')->default(0);
                }
            });
        }
    }
};
