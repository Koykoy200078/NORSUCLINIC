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
        try {
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

        // Create optimized indexes only if they don't exist
        $existingIndexes = DB::select("SHOW INDEX FROM appointments WHERE Key_name IN ('idx_appointments_date_50', 'idx_appointments_date_status_opt', 'idx_appointments_doctor_status', 'idx_appointments_patient_status')");
        $existingIndexNames = array_unique(array_column($existingIndexes, 'Key_name'));

        if (!in_array('idx_appointments_date_50', $existingIndexNames)) {
            DB::statement('CREATE INDEX idx_appointments_date_50 ON appointments (date(50))');
        }

        if (!in_array('idx_appointments_date_status_opt', $existingIndexNames)) {
            DB::statement('CREATE INDEX idx_appointments_date_status_opt ON appointments (date(50), status)');
        }

        if (!in_array('idx_appointments_doctor_status', $existingIndexNames)) {
            DB::statement('CREATE INDEX idx_appointments_doctor_status ON appointments (doctor_id, status)');
        }

        if (!in_array('idx_appointments_patient_status', $existingIndexNames)) {
            DB::statement('CREATE INDEX idx_appointments_patient_status ON appointments (patient_id, status)');
        }
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
