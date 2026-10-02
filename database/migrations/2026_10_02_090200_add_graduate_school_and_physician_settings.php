<?php

use App\Models\College;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * ACCOMPLISHMENT REPORT: a Graduate School column (GS) and the University Physician who signs "Noted by".
 * Colleges and settings have no admin screen that could create these, so they are added here (and in the seeders
 * for fresh installs). Safe to run on a copy that already has them.
 */
return new class extends Migration
{
    public function up(): void
    {
        College::firstOrCreate(['college_name' => 'Graduate School (GS)']);

        Setting::firstOrCreate(['key' => 'university_physician_name'], ['value' => '']);
        Setting::firstOrCreate(['key' => 'university_physician_title'], ['value' => 'University Physician']);
    }

    public function down(): void
    {
        Setting::whereIn('key', ['university_physician_name', 'university_physician_title'])->delete();
        // The Graduate School row is kept: consultations may already point at it.
    }
};
