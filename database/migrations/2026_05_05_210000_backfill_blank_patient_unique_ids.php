<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patients') || ! Schema::hasColumn('patients', 'patient_unique_id')) {
            return;
        }

        $rows = DB::table('patients')
            ->select('id')
            ->where(function ($query) {
                $query->whereNull('patient_unique_id')
                    ->orWhereRaw("TRIM(patient_unique_id) = ''");
            })
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            do {
                $identifier = 'PT' . now()->format('YmdHisv') . Str::upper(Str::random(3));

                $exists = DB::table('patients')
                    ->where('patient_unique_id', $identifier)
                    ->where('id', '!=', $row->id)
                    ->exists();
            } while ($exists);

            DB::table('patients')
                ->where('id', $row->id)
                ->update(['patient_unique_id' => $identifier]);
        }
    }

    public function down(): void
    {
        // No-op: previous blank identifiers cannot be reconstructed safely.
    }
};
