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
        Schema::table('patients', function (Blueprint $table) {
            $table->text('allergies')->nullable();
            $table->text('comorbidities')->nullable();
            $table->text('admissions_surgeries')->nullable();
            $table->text('maintenance')->nullable();
            $table->string('covid_vaccination')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'allergies',
                'comorbidities',
                'admissions_surgeries',
                'maintenance',
                'covid_vaccination'
            ]);
        });
    }
};
