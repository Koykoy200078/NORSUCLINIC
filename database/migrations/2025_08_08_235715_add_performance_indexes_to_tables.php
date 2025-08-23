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
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email'], 'idx_users_email');
            $table->index(['status'], 'idx_users_status');
            $table->index(['type'], 'idx_users_type');
            $table->index(['campus_id', 'college_id'], 'idx_users_campus_college');
        });

        // Appointments table indexes
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->index(['patient_id'], 'idx_appointments_patient');
                $table->index(['doctor_id'], 'idx_appointments_doctor');
                $table->index(['date'], 'idx_appointments_date');
                $table->index(['status'], 'idx_appointments_status');
            });
        }

        // Prescriptions table indexes
        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->index(['patient_id'], 'idx_prescriptions_patient');
                $table->index(['doctor_id'], 'idx_prescriptions_doctor');
                $table->index(['appointment_id'], 'idx_prescriptions_appointment');
            });
        }

        // Medicine related indexes
        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->index(['category_id'], 'idx_medicines_category');
                $table->index(['name'], 'idx_medicines_name');
            });
        }

        if (Schema::hasTable('medicine_bills')) {
            Schema::table('medicine_bills', function (Blueprint $table) {
                $table->index(['patient_id'], 'idx_medicine_bills_patient');
                $table->index(['doctor_id'], 'idx_medicine_bills_doctor');
            });
        }

        // Patients table indexes
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_patients_user');
                $table->index(['patient_unique_id'], 'idx_patients_unique');
            });
        }

        // Doctors table indexes
        if (Schema::hasTable('doctors')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->index(['user_id'], 'idx_doctors_user');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_type');
            $table->dropIndex('idx_users_campus_college');
        });

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('idx_appointments_patient');
                $table->dropIndex('idx_appointments_doctor');
                $table->dropIndex('idx_appointments_date');
                $table->dropIndex('idx_appointments_status');
            });
        }

        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropIndex('idx_prescriptions_patient');
                $table->dropIndex('idx_prescriptions_doctor');
                $table->dropIndex('idx_prescriptions_appointment');
            });
        }

        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropIndex('idx_medicines_category');
                $table->dropIndex('idx_medicines_name');
            });
        }

        if (Schema::hasTable('medicine_bills')) {
            Schema::table('medicine_bills', function (Blueprint $table) {
                $table->dropIndex('idx_medicine_bills_patient');
                $table->dropIndex('idx_medicine_bills_doctor');
            });
        }

        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropIndex('idx_patients_user');
                $table->dropIndex('idx_patients_unique');
            });
        }

        if (Schema::hasTable('doctors')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropIndex('idx_doctors_user');
            });
        }
    }
};
