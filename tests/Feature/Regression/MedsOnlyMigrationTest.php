<?php

namespace Tests\Feature\Regression;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * The Meds Only columns on `medicine_bills` (2026-10-29), on a throwaway schema shaped like the clinic's table: existing
 * rows are not touched, running the migration again changes nothing, and rolling it back removes only what it added.
 * (A throwaway schema because ALTER TABLE commits: on the shared test schema it would leave rows behind.)
 */
class MedsOnlyMigrationTest extends TestCase
{
    use UsesFixtureDatabase;

    private const FILE = '2026_10_29_100000_add_meds_only_to_dispense_records.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->useFixtureDatabase('meds_only');

        DB::statement('CREATE TABLE medicine_bills (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            history_number VARCHAR(191) NOT NULL,
            patient_id BIGINT UNSIGNED NOT NULL,
            doctor_id BIGINT UNSIGNED NULL,
            model_type VARCHAR(191) NOT NULL,
            model_id VARCHAR(191) NULL,
            note TEXT NULL,
            bill_date DATETIME NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            UNIQUE KEY uq_medicine_bills_history_number (history_number),
            KEY idx_medicine_bills_patient (patient_id)
        ) ENGINE=InnoDB');
        DB::statement('CREATE TABLE sale_medicines (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, medicine_bill_id BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB');
        DB::table('medicine_bills')->insert([
            ['history_number' => 'HIS1', 'patient_id' => 7, 'model_type' => 'App\\Models\\DispenseRecord', 'note' => 'keep me', 'bill_date' => '2026-03-01 00:00:00'],
            ['history_number' => 'HIS2', 'patient_id' => 8, 'model_type' => 'App\\Models\\Prescription', 'note' => null, 'bill_date' => '2026-03-02 00:00:00'],
        ]);
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    private function columns(): array
    {
        return collect(DB::select('SHOW COLUMNS FROM medicine_bills'))->pluck('Type', 'Field')->all();
    }

    private function indexCount(): int
    {
        return DB::table('information_schema.statistics')->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'medicine_bills')->where('index_name', 'idx_medicine_bills_meds_only_date')->distinct()->count('index_name');
    }

    public function test_the_existing_rows_are_untouched_and_a_record_without_a_patient_is_possible(): void
    {
        $before = DB::table('medicine_bills')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();

        $this->loadMigration(self::FILE)->up();

        $after = DB::table('medicine_bills')->orderBy('id')->get();
        foreach ($before as $i => $old) {
            foreach ($old as $column => $value) {
                $this->assertEquals($value, $after[$i]->{$column}, "row {$old['id']} column {$column}");
            }
            $this->assertSame(0, (int) $after[$i]->meds_only);
            $this->assertNull($after[$i]->patient_name);
        }

        DB::table('medicine_bills')->insert(['history_number' => 'HIS3', 'patient_id' => null, 'model_type' => 'App\\Models\\DispenseRecord',
            'bill_date' => '2026-03-03 00:00:00', 'meds_only' => 1, 'patient_name' => 'Typed Name', 'college' => 'College X']);
        $this->assertSame('Typed Name', DB::table('medicine_bills')->where('history_number', 'HIS3')->value('patient_name'));
    }

    public function test_running_it_again_changes_nothing(): void
    {
        $migration = $this->loadMigration(self::FILE);
        $migration->up();
        $columns = $this->columns();

        $migration->up();

        $this->assertSame($columns, $this->columns());
        $this->assertSame(1, $this->indexCount(), 'the index is made once');
        $this->assertCount(2, DB::table('medicine_bills')->get());
    }

    public function test_rolling_it_back_refuses_while_a_meds_only_record_exists_and_changes_nothing(): void
    {
        $migration = $this->loadMigration(self::FILE);
        $migration->up();
        DB::table('medicine_bills')->insert(['history_number' => 'HIS3', 'patient_id' => null, 'model_type' => 'App\\Models\\DispenseRecord',
            'bill_date' => '2026-03-03 00:00:00', 'meds_only' => 1, 'patient_name' => 'Typed Name']);
        DB::table('sale_medicines')->insert([['medicine_bill_id' => 1], ['medicine_bill_id' => 3]]);
        $columns = $this->columns();

        $refused = false;
        try {
            $migration->down();
        } catch (\RuntimeException $e) {
            $refused = true;
            $this->assertStringContainsString('Meds Only records exist', $e->getMessage());
        }
        $this->assertTrue($refused, 'the rollback must refuse: it would delete dispense records whose stock was already taken out');

        // Nothing was removed, not a record, not a line, not a column, not the index.
        $this->assertSame(['HIS1', 'HIS2', 'HIS3'], DB::table('medicine_bills')->orderBy('id')->pluck('history_number')->all());
        $this->assertSame([1, 3], DB::table('sale_medicines')->orderBy('id')->pluck('medicine_bill_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame($columns, $this->columns());
        $this->assertSame(1, $this->indexCount());
        $this->assertSame('Typed Name', DB::table('medicine_bills')->where('history_number', 'HIS3')->value('patient_name'));
    }

    public function test_rolling_it_back_without_any_meds_only_record_removes_only_the_new_columns(): void
    {
        $migration = $this->loadMigration(self::FILE);
        $migration->up();
        DB::table('sale_medicines')->insert([['medicine_bill_id' => 1], ['medicine_bill_id' => 2]]);

        $migration->down();

        $this->assertSame(['HIS1', 'HIS2'], DB::table('medicine_bills')->orderBy('id')->pluck('history_number')->all(), 'the older records are untouched');
        $this->assertSame([1, 2], DB::table('sale_medicines')->orderBy('id')->pluck('medicine_bill_id')->map(fn ($id) => (int) $id)->all());
        foreach (['meds_only', 'patient_name', 'college', 'recorded_by'] as $column) {
            $this->assertFalse(Schema::hasColumn('medicine_bills', $column), $column);
        }
        $this->assertSame('NO', collect(DB::select('SHOW COLUMNS FROM medicine_bills'))->firstWhere('Field', 'patient_id')->Null);
        $this->assertSame(0, $this->indexCount());
    }
}
