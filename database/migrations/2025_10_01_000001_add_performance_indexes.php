<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for User table to optimize role/type queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['type', 'status'], 'idx_users_type_status');
            $table->index(['type', 'created_at'], 'idx_users_type_created');
            $table->index('email_verified_at', 'idx_users_email_verified');
        });

        // Add indexes for Appointment table to optimize dashboard queries
        Schema::table('appointments', function (Blueprint $table) {
            // Use raw SQL for date prefix index to avoid 1000 byte limit on VARCHAR date column
            DB::statement('CREATE INDEX idx_appointments_date_status ON appointments (date(50), status)');
            $table->index(['doctor_id', 'status'], 'idx_appointments_doctor_status');
            $table->index(['patient_id', 'status'], 'idx_appointments_patient_status');
            $table->index(['created_at', 'status'], 'idx_appointments_created_status');
        });

        // Add indexes for Patient table
        Schema::table('patients', function (Blueprint $table) {
            $table->index('created_at', 'idx_patients_created_at');
            $table->index('user_id', 'idx_patients_user_id');
        });

        // Add indexes for Service and ServiceCategory tables
        Schema::table('services', function (Blueprint $table) {
            $table->index('status', 'idx_services_status');
        });

        // Add indexes for Settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->index('key', 'idx_settings_key');
        });

        // Add indexes for Transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_transactions_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type_status');
            $table->dropIndex('idx_users_type_created');
            $table->dropIndex('idx_users_email_verified');
        });

        Schema::table('appointments', function (Blueprint $table) {
            DB::statement('DROP INDEX IF EXISTS idx_appointments_date_status ON appointments');
            $table->dropIndex('idx_appointments_doctor_status');
            $table->dropIndex('idx_appointments_patient_status');
            $table->dropIndex('idx_appointments_created_status');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_created_at');
            $table->dropIndex('idx_patients_user_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_status');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex('idx_settings_key');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_status_created');
        });
    }
};
