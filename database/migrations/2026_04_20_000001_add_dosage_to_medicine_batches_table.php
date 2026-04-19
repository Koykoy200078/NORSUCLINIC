<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('medicine_batches')) {
            return;
        }

        if (! Schema::hasColumn('medicine_batches', 'dosage')) {
            Schema::table('medicine_batches', function (Blueprint $table) {
                $table->string('dosage', 100)->nullable()->after('batch_number');
            });
        }

        if (Schema::hasColumn('medicine_batches', 'dosage') && Schema::hasTable('medicines') && Schema::hasColumn('medicines', 'dosage')) {
            DB::statement(
                "UPDATE medicine_batches mb
                 INNER JOIN medicines m ON m.id = mb.medicine_id
                 SET mb.dosage = COALESCE(NULLIF(mb.dosage, ''), NULLIF(m.dosage, ''), 'N/A')"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('medicine_batches')) {
            return;
        }

        if (Schema::hasColumn('medicine_batches', 'dosage')) {
            Schema::table('medicine_batches', function (Blueprint $table) {
                $table->dropColumn('dosage');
            });
        }
    }
};
