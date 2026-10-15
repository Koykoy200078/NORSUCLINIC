<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plan item 5.23: a ticked "Others" service records what was actually done, as an "Others" illness already does
 * (consultation_illnesses.other_text). Optional and additive: existing rows stay NULL ("no name given").
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consultation_services') && ! Schema::hasColumn('consultation_services', 'other_text')) {
            Schema::table('consultation_services', function (Blueprint $table) {
                $table->string('other_text', 150)->nullable()->after('service_type_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('consultation_services') && Schema::hasColumn('consultation_services', 'other_text')) {
            Schema::table('consultation_services', function (Blueprint $table) {
                $table->dropColumn('other_text');
            });
        }
    }
};
