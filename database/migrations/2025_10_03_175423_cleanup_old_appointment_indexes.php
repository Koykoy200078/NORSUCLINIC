<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration removes old redundant indexes that were replaced
     * by optimized indexes in migration 2025_10_03_174644_fix_appointments_date_status_index
     */
    public function up(): void
    {
        // Check which indexes exist first
        $existingIndexes = DB::select("SHOW INDEX FROM appointments WHERE Key_name IN ('idx_appointments_doctor_date_status', 'idx_appointments_patient_date_status')");
        $existingIndexNames = array_unique(array_column($existingIndexes, 'Key_name'));

        // Drop old composite indexes that include the VARCHAR date column
        // These are redundant and less efficient than the new optimized indexes

        if (in_array('idx_appointments_doctor_date_status', $existingIndexNames)) {
            try {
                DB::statement('ALTER TABLE appointments DROP INDEX idx_appointments_doctor_date_status');
                echo "✓ Dropped idx_appointments_doctor_date_status\n";
            } catch (\Exception $e) {
                echo "✗ Failed to drop idx_appointments_doctor_date_status: " . $e->getMessage() . "\n";
            }
        } else {
            echo "ℹ Index idx_appointments_doctor_date_status does not exist\n";
        }

        if (in_array('idx_appointments_patient_date_status', $existingIndexNames)) {
            try {
                DB::statement('ALTER TABLE appointments DROP INDEX idx_appointments_patient_date_status');
                echo "✓ Dropped idx_appointments_patient_date_status\n";
            } catch (\Exception $e) {
                echo "✗ Failed to drop idx_appointments_patient_date_status: " . $e->getMessage() . "\n";
            }
        } else {
            echo "ℹ Index idx_appointments_patient_date_status does not exist\n";
        }

        echo "\n✓ Cleanup complete! Old redundant indexes removed.\n";
        echo "  Remaining optimized indexes:\n";
        echo "  - idx_appointments_date_50 (date(50))\n";
        echo "  - idx_appointments_date_status_opt (date(50), status)\n";
        echo "  - idx_appointments_doctor_status (doctor_id, status)\n";
        echo "  - idx_appointments_patient_status (patient_id, status)\n";
        echo "  - idx_appointments_created_status (created_at, status)\n";
    }

    /**
     * Reverse the migrations.
     * 
     * Note: We don't recreate the old indexes on rollback because they were
     * problematic (exceeded key length) and are replaced by better indexes.
     */
    public function down(): void
    {
        // Intentionally left empty - we don't want to recreate the problematic indexes
        echo "ℹ Rollback: Not recreating old indexes (they were problematic)\n";
    }
};
