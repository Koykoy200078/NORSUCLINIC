<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop all tables and columns from features removed in Phases 1–5.
     * Dropped in FK-safe order (children before parents).
     */
    public function up(): void
    {
        // Phase 3: Visits and their child tables
        Schema::dropIfExists('visit_problems');
        Schema::dropIfExists('visit_observations');
        Schema::dropIfExists('visit_notes');
        Schema::dropIfExists('visit_prescriptions');
        Schema::dropIfExists('visits');

        // Phase 2: Appointments first (FK → services)
        Schema::dropIfExists('appointments');

        // Phase 2: Services (after appointments)
        Schema::dropIfExists('service_doctor');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');

        // Phase 2: Doctor sessions
        Schema::dropIfExists('session_week_days');
        Schema::dropIfExists('doctor_sessions');
        Schema::dropIfExists('clinic_schedules');

        // Phase 1: Payments
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('currencies');

        // Phase 2: Holidays (clinic closed days - no longer tracked)
        Schema::dropIfExists('holidays');

        // Phase 2: Column cleanup on kept tables
        if (Schema::hasColumn('prescriptions', 'appointment_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_prescriptions_appointment');
                } catch (\Exception $e) {
                    // Index may not exist in all environments
                }
                $table->dropColumn('appointment_id');
            });
        }
    }

    public function down(): void
    {
        // Restore prescriptions column
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
        });

        // Note: Restoring 15 dropped tables would require recreating their full schemas.
        // See the original creation migration files for table definitions.
    }
};
