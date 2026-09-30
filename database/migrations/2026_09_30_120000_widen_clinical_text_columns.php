<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P2-H4: free-text clinical fields were VARCHAR(100)/VARCHAR(191), while the forms and
 * validation accept far more. With MySQL running in non-strict mode the surplus was cut off
 * silently (the end of an allergy list, "STOP IF RASH APPEARS", ...). Widen them to TEXT.
 *
 * Only VARCHAR -> TEXT (a widening change), nullability is preserved, and columns that are
 * already TEXT-like are left alone, so the migration is safe to run more than once.
 */
return new class extends Migration
{
    /** table => [column => nullable] */
    private const COLUMNS = [
        'document_issuances' => [
            'allergies' => true,
            'comorbidities' => true,
            'admissions_surgeries' => true,
            'maintenance' => true,
            'nursing_intervention' => true,
            'address' => false,
            'emergency_contact' => true,
            'pregnancy_status' => true,
            'lmp_aog' => true,
        ],
        'lab_requests' => [
            'clinical_indication' => true,
            'address' => true,
        ],
        'prescriptions' => [
            'advice' => true,
            'problem_description' => true,
            'test' => true,
            'current_medication' => true,
            'food_allergies' => true,
        ],
        'prescriptions_medicines' => [
            'comment' => true,
        ],
    ];

    public function up(): void
    {
        // The test-suite runs on SQLite, where VARCHAR length is not enforced anyway.
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column => $nullable) {
                if (! Schema::hasColumn($table, $column) || ! $this->isVarchar($table, $column)) {
                    continue;
                }

                DB::statement(sprintf(
                    'ALTER TABLE `%s` MODIFY `%s` TEXT %s',
                    $table,
                    $column,
                    $nullable ? 'NULL' : 'NOT NULL'
                ));
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: shrinking the columns again would truncate stored clinical text.
    }

    private function isVarchar(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT DATA_TYPE AS data_type FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row !== null && strtolower((string) $row->data_type) === 'varchar';
    }
};
