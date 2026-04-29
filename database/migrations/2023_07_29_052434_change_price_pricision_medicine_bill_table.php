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

        $hasMedicineBillDiscount = Schema::hasColumn('medicine_bills', 'discount');
        $hasMedicineBillNetAmount = Schema::hasColumn('medicine_bills', 'net_amount');
        $hasMedicineBillTotal = Schema::hasColumn('medicine_bills', 'total');
        $hasMedicineBillTaxAmount = Schema::hasColumn('medicine_bills', 'tax_amount');
        $hasMedicineBillDate = Schema::hasColumn('medicine_bills', 'bill_date');

        Schema::table('medicine_bills', function (Blueprint $table) use (
            $hasMedicineBillDiscount,
            $hasMedicineBillNetAmount,
            $hasMedicineBillTotal,
            $hasMedicineBillTaxAmount,
            $hasMedicineBillDate
        ) {
            if ($hasMedicineBillDiscount) {
                $table->float('discount', 25, 2)->change();
            }
            if ($hasMedicineBillNetAmount) {
                $table->float('net_amount', 25, 2)->change();
            }
            if ($hasMedicineBillTotal) {
                $table->float('total', 25, 2)->change();
            }
            if ($hasMedicineBillTaxAmount) {
                $table->float('tax_amount', 25, 2)->change();
            }
            if (! $hasMedicineBillDate) {
                $table->datetime('bill_date')->after('note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
