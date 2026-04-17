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
        if (! Schema::hasTable('patients')) {
            return;
        }

        Schema::table('patients', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('patients', 'campus_address')) {
                $dropColumns[] = 'campus_address';
            }

            if (Schema::hasColumn('patients', 'permanent_address')) {
                $dropColumns[] = 'permanent_address';
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('patients')) {
            return;
        }

        Schema::table('patients', function (Blueprint $table) {
            if (! Schema::hasColumn('patients', 'campus_address')) {
                $table->text('campus_address')->nullable();
            }

            if (! Schema::hasColumn('patients', 'permanent_address')) {
                $table->text('permanent_address')->nullable();
            }
        });
    }
};
