<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plan Phase 4.2: the College of Law is "COL", not "CL".
 *
 * The college is renamed where it is a list entry (`colleges`, whose bracketed code is the column heading of the
 * Accomplishment Report) and where its name was saved as text next to a record: a consultation, a lab request and an
 * activity-log line keep the college name they were made under, and ConsultationSnapshotBackfill matches such texts
 * to the list by name, so an old consultation would stop finding its college if only the list changed.
 *
 * Only the exact old name is touched, so a second run (or a database that never had the college) changes nothing.
 */
return new class extends Migration
{
    private const OLD = 'College of Law (CL)';

    private const NEW = 'College of Law (COL)';

    /** @var array<string, string> table => the column that holds the college name as text */
    private const COLUMNS = [
        'colleges' => 'college_name',
        'document_issuances' => 'college',
        'lab_requests' => 'college',
        'activity_logs' => 'college',
    ];

    public function up(): void
    {
        $this->rename(self::OLD, self::NEW);
    }

    public function down(): void
    {
        $this->rename(self::NEW, self::OLD);
    }

    private function rename(string $from, string $to): void
    {
        DB::transaction(function () use ($from, $to) {
            foreach (self::COLUMNS as $table => $column) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                    DB::table($table)->where($column, $from)->update([$column => $to]);
                }
            }
        });
    }
};
