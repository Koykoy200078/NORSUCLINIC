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
        // Drop problematic indexes if they exist
        Schema::table('appointments', function (Blueprint $table) {
            try {
                // Try to drop the problematic composite indexes that exceed key length
                DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_date_status');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_doctor_date_status');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_patient_date_status');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        });

        // Add optimized indexes with prefix length for VARCHAR date column
        DB::statement('CREATE INDEX idx_appointments_date_50 ON appointments (date(50))');
        DB::statement('CREATE INDEX idx_appointments_date_status_opt ON appointments (date(50), status)');
        DB::statement('CREATE INDEX idx_appointments_doctor_status ON appointments (doctor_id, status)');
        DB::statement('CREATE INDEX idx_appointments_patient_status ON appointments (patient_id, status)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_date_50');
        DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_date_status_opt');
        DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_doctor_status');
        DB::statement('ALTER TABLE appointments DROP INDEX IF EXISTS idx_appointments_patient_status');
    }
};
