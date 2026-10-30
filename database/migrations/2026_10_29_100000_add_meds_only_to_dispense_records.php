<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Meds Only (owner request 2026-10-29): a patient who only comes for medicine, with no consultation. It is saved as a
 * dispense record (`medicine_bills` + `sale_medicines`), so the stock ledger, Stock Out, Dispense History and the
 * Dispensing / Medicine Inventory reports already know it. What a dispense record lacked:
 *
 *  - a person who is not registered: `patient_id` may now be empty and the name is typed (`patient_name`);
 *  - what the reports group by: campus / college / course / year level / patient type, as the ids of the lists and as the
 *    text of the day (the same pair `document_issuances` keeps, so a later rename of a list never rewrites history);
 *  - who handed it out (`recorded_by`, a dispense record had no author);
 *  - a way to tell the new records from the manual ones: `meds_only` (0 for every record that exists today).
 *
 * Nothing is changed in the existing rows. Safe to run again: every column is added only when it is missing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medicine_bills')) {
            return;
        }

        Schema::table('medicine_bills', function (Blueprint $table) {
            if (! Schema::hasColumn('medicine_bills', 'meds_only')) {
                $table->boolean('meds_only')->default(false)->after('bill_date');
            }
            if (! Schema::hasColumn('medicine_bills', 'patient_name')) {
                $table->string('patient_name', 191)->nullable()->after('meds_only');
            }
            foreach (['campus_id', 'college_id', 'course_id', 'year_level_id', 'patient_type_id', 'recorded_by'] as $column) {
                if (! Schema::hasColumn('medicine_bills', $column)) {
                    $table->unsignedBigInteger($column)->nullable();
                }
            }
            foreach (['campus', 'college', 'course', 'year_level'] as $column) {
                if (! Schema::hasColumn('medicine_bills', $column)) {
                    $table->string($column, 191)->nullable();
                }
            }
        });

        // A typed name has no patient row.
        DB::statement('ALTER TABLE medicine_bills MODIFY patient_id BIGINT UNSIGNED NULL');

        if (! $this->indexExists('idx_medicine_bills_meds_only_date')) {
            Schema::table('medicine_bills', function (Blueprint $table) {
                $table->index(['meds_only', 'bill_date'], 'idx_medicine_bills_meds_only_date');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('medicine_bills')) {
            return;
        }

        // A record without a patient cannot go back to a column that requires one, and deleting it here would leave its
        // stock taken out of the shelf with nothing to explain it (the units only come back through MedsOnlyService::delete).
        // So the rollback refuses while any Meds Only record exists, as the count-sheet migration does, before it changes anything.
        if (Schema::hasColumn('medicine_bills', 'meds_only') && DB::table('medicine_bills')->where('meds_only', 1)->exists()) {
            throw new RuntimeException('Meds Only records exist; this rollback would delete them without returning their stock. '
                . 'Delete them in Meds Only first (that puts the units back), or keep the migration.');
        }

        if ($this->indexExists('idx_medicine_bills_meds_only_date')) {
            Schema::table('medicine_bills', fn (Blueprint $table) => $table->dropIndex('idx_medicine_bills_meds_only_date'));
        }

        $columns = array_values(array_filter(
            ['meds_only', 'patient_name', 'campus_id', 'college_id', 'course_id', 'year_level_id', 'patient_type_id', 'recorded_by', 'campus', 'college', 'course', 'year_level'],
            fn (string $column) => Schema::hasColumn('medicine_bills', $column)
        ));
        if ($columns !== []) {
            Schema::table('medicine_bills', fn (Blueprint $table) => $table->dropColumn($columns));
        }

        DB::statement('ALTER TABLE medicine_bills MODIFY patient_id BIGINT UNSIGNED NOT NULL');
    }

    private function indexExists(string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'medicine_bills')
            ->where('index_name', $name)
            ->exists();
    }
};
