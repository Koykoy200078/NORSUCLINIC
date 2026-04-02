<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // DefaultHolidayPermissionSeeder and DefaultClinicSchedulesSeeder removed in Phase 7 cleanup.
        // Holiday and ClinicSchedule features were removed in Phase 2.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
