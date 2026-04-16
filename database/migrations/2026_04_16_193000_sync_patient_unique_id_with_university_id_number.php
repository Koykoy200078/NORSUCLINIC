<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patients') || ! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('patients', 'patient_unique_id') || ! Schema::hasColumn('users', 'university_id_number')) {
            return;
        }

        $rows = DB::table('patients')
            ->join('users', 'users.id', '=', 'patients.user_id')
            ->select('patients.id', 'patients.patient_unique_id', 'users.university_id_number')
            ->orderBy('patients.id')
            ->get();

        foreach ($rows as $row) {
            $identifier = strtoupper(trim((string) $row->university_id_number));

            if ($identifier === '' || $identifier === $row->patient_unique_id) {
                continue;
            }

            $isDuplicate = DB::table('patients')
                ->where('patient_unique_id', $identifier)
                ->where('id', '!=', $row->id)
                ->exists();

            if ($isDuplicate) {
                continue;
            }

            DB::table('patients')
                ->where('id', $row->id)
                ->update(['patient_unique_id' => $identifier]);
        }
    }

    public function down(): void
    {
        // Intentionally left empty: this is a data synchronization migration.
    }
};
