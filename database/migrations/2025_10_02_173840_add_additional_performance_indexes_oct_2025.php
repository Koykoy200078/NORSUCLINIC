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
        // Add indexes for Doctors table
        Schema::table('doctors', function (Blueprint $table) {
            $table->index('user_id', 'idx_doctors_user_id');
            $table->index('created_at', 'idx_doctors_created_at');
        });

        // Add indexes for Medicine Bills table
        Schema::table('medicine_bills', function (Blueprint $table) {
            $table->index('patient_id', 'idx_medicine_bills_patient_id');
            $table->index('doctor_id', 'idx_medicine_bills_doctor_id');
            $table->index('created_at', 'idx_medicine_bills_created_at');
            $table->index('history_number', 'idx_medicine_bills_history_number');
        });

        // Add indexes for Medicines table
        Schema::table('medicines', function (Blueprint $table) {
            $table->index('category_id', 'idx_medicines_category_id');
            $table->index('brand_id', 'idx_medicines_brand_id');
        });

        // Add indexes for Prescriptions table
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index('patient_id', 'idx_prescriptions_patient_id');
            $table->index('doctor_id', 'idx_prescriptions_doctor_id');
            $table->index('created_at', 'idx_prescriptions_created_at');
        });

        // Add indexes for Visits table
        Schema::table('visits', function (Blueprint $table) {
            $table->index('patient_id', 'idx_visits_patient_id');
            $table->index('doctor_id', 'idx_visits_doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('idx_doctors_user_id');
            $table->dropIndex('idx_doctors_created_at');
        });

        Schema::table('medicine_bills', function (Blueprint $table) {
            $table->dropIndex('idx_medicine_bills_patient_id');
            $table->dropIndex('idx_medicine_bills_doctor_id');
            $table->dropIndex('idx_medicine_bills_created_at');
            $table->dropIndex('idx_medicine_bills_history_number');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex('idx_medicines_category_id');
            $table->dropIndex('idx_medicines_brand_id');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex('idx_prescriptions_patient_id');
            $table->dropIndex('idx_prescriptions_doctor_id');
            $table->dropIndex('idx_prescriptions_created_at');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('idx_visits_patient_id');
            $table->dropIndex('idx_visits_doctor_id');
        });
    }
};
