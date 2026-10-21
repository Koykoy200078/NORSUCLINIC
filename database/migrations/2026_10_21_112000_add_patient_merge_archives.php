<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('patients', 'merged_into_id')) {
            Schema::table('patients', function (Blueprint $table): void {
                $table->unsignedInteger('merged_into_id')->nullable();
                $table->timestamp('merged_at')->nullable();
                $table->unsignedBigInteger('merged_by')->nullable();
                $table->foreign('merged_into_id')->references('id')->on('patients')->restrictOnDelete();
            });
        }
        if (! Schema::hasColumn('addresses', 'merged_from_patient_id')) {
            Schema::table('addresses', function (Blueprint $table): void {
                $table->unsignedInteger('merged_from_patient_id')->nullable();
                $table->foreign('merged_from_patient_id')->references('id')->on('patients')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropForeign(['merged_from_patient_id']);
            $table->dropColumn('merged_from_patient_id');
        });
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropForeign(['merged_into_id']);
            $table->dropColumn(['merged_into_id', 'merged_at', 'merged_by']);
        });
    }
};
