<?php

namespace Tests\Feature\Regression;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyMigrationWriterReadsTest extends TestCase
{
    private ?Connection $adminConnection = null;
    private array $fixtureDatabases = [];
    private string $originalConnection;
    private string $writerDatabase;
    private string $readerDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();
        $this->adminConnection = DB::connection();
        if ($this->adminConnection->getDriverName() !== 'mysql') {
            $this->markTestSkipped('This regression requires MySQL/MariaDB.');
        }

        $token = bin2hex(random_bytes(6));
        $this->writerDatabase = 'legacy_writer_'.$token.'_test';
        $this->readerDatabase = 'legacy_reader_'.$token.'_test';
        foreach ([$this->writerDatabase, $this->readerDatabase] as $database) {
            $this->adminConnection->statement('CREATE DATABASE `'.$database.'`');
            $this->fixtureDatabases[] = $database;
        }

        // Separate local schemas model a writer and a replica whose DDL is delayed.
        // They never reuse the application tables or the shared suite's test tables.
        config(['database.connections.legacy_writer_reads_fixture' => array_merge(
            $this->adminConnection->getConfig(),
            [
                'database' => $this->writerDatabase,
                'url' => null,
                'prefix' => '',
                'sticky' => false,
                'read' => ['database' => $this->readerDatabase],
                'write' => ['database' => $this->writerDatabase],
            ]
        )]);
        DB::setDefaultConnection('legacy_writer_reads_fixture');
    }

    protected function tearDown(): void
    {
        try {
            if ($this->fixtureDatabases !== []) {
                DB::purge('legacy_writer_reads_fixture');
                DB::setDefaultConnection($this->originalConnection);
                foreach (array_reverse($this->fixtureDatabases) as $database) {
                    $this->adminConnection->statement('DROP DATABASE `'.$database.'`');
                }
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_sale_indexes_use_writer_metadata_when_a_replica_lags_a_partial_upgrade(): void
    {
        foreach ([$this->writerDatabase, $this->readerDatabase] as $database) {
            $this->adminConnection->statement('CREATE TABLE `'.$database.'`.`sale_medicines` (
                `id` INT PRIMARY KEY, `medicine_bill_id` BIGINT UNSIGNED NOT NULL,
                `medicine_id` BIGINT UNSIGNED NOT NULL
            )');
        }
        DB::statement('ALTER TABLE `sale_medicines` ADD INDEX `idx_sale_medicines_bill` (`medicine_bill_id`)');
        $originalMode = $this->sqlMode();

        $migration = $this->migration('2026_09_30_150000_add_sale_medicines_indexes.php');
        $migration->up();
        $migration->up();

        $indexes = DB::selectFromWriteConnection('SHOW INDEX FROM `sale_medicines`');
        $this->assertSame(1, collect($indexes)->where('Key_name', 'idx_sale_medicines_bill')->count());
        $this->assertSame(1, collect($indexes)->where('Key_name', 'idx_sale_medicines_medicine')->count());
        $this->assertSame(0, collect(DB::select('SHOW INDEX FROM `sale_medicines`'))->where('Key_name', 'idx_sale_medicines_bill')->count());
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_history_foreign_keys_are_restricted_even_when_the_replica_has_no_constraint_yet(): void
    {
        foreach ([$this->writerDatabase, $this->readerDatabase] as $database) {
            $this->adminConnection->statement('CREATE TABLE `'.$database.'`.`medicines` (`id` BIGINT UNSIGNED PRIMARY KEY) ENGINE=InnoDB');
            $this->adminConnection->statement('CREATE TABLE `'.$database.'`.`prescriptions_medicines` (
                `id` BIGINT UNSIGNED PRIMARY KEY, `medicine` BIGINT UNSIGNED NOT NULL
            ) ENGINE=InnoDB');
        }
        DB::statement('ALTER TABLE `prescriptions_medicines` ADD CONSTRAINT `legacy_rx_medicine_foreign`
            FOREIGN KEY (`medicine`) REFERENCES `medicines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        DB::table('medicines')->insert(['id' => 1]);
        DB::table('prescriptions_medicines')->insert(['id' => 1, 'medicine' => 1]);
        $originalMode = $this->sqlMode();

        $this->migration('2026_09_30_141000_restrict_delete_of_medicines_with_history.php')->up();

        $constraint = DB::selectOne('SELECT DELETE_RULE AS delete_rule, UPDATE_RULE AS update_rule
            FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ?', ['legacy_rx_medicine_foreign'], false);
        $this->assertSame('RESTRICT', $constraint->delete_rule);
        $this->assertSame('CASCADE', $constraint->update_rule);
        $this->assertSame(1, DB::table('prescriptions_medicines')->useWritePdo()->count());
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_prescription_foreign_key_migration_can_be_previewed_without_select_results(): void
    {
        $originalMode = $this->sqlMode();
        $queries = DB::connection()->pretend(function () {
            $this->migration('2026_09_30_130000_restrict_delete_of_doctors_and_patients_with_prescriptions.php')->up();
        });

        $this->assertCount(2, $queries);
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_a_lagging_reader_cannot_expose_deleted_clinical_records_during_rollback(): void
    {
        $this->assertRollbackKeepsDeletedRecords(
            '2026_10_02_130000_add_soft_deletes_to_document_issuances.php',
            'document_issuances',
            'document_issuances_deleted_at_index'
        );
    }

    public function test_a_lagging_reader_cannot_expose_deleted_lab_requests_during_rollback(): void
    {
        $this->assertRollbackKeepsDeletedRecords(
            '2026_10_02_150000_lab_request_soft_delete_and_unique_stock_in_numbers.php',
            'lab_requests',
            'lab_requests_deleted_at_index'
        );
    }

    public function test_settings_deduplication_uses_writer_rows_and_keeps_the_oldest_value(): void
    {
        foreach ([$this->writerDatabase, $this->readerDatabase] as $database) {
            $this->adminConnection->statement('CREATE TABLE `'.$database.'`.`settings` (
                `id` BIGINT UNSIGNED PRIMARY KEY, `key` VARCHAR(191) NOT NULL,
                `value` VARCHAR(191) NOT NULL
            )');
        }
        DB::table('settings')->insert([
            ['id' => 1, 'key' => 'clinic_name', 'value' => 'Original clinic'],
            ['id' => 2, 'key' => 'clinic_name', 'value' => 'Duplicate clinic'],
        ]);
        $originalMode = $this->sqlMode();

        $this->migration('2026_09_30_170000_make_settings_key_unique.php')->up();

        $rows = DB::table('settings')->useWritePdo()->get();
        $this->assertCount(1, $rows);
        $this->assertSame(1, (int) $rows->first()->id);
        $this->assertSame('Original clinic', $rows->first()->value);
        $index = collect(DB::selectFromWriteConnection('SHOW INDEX FROM `settings`'))
            ->firstWhere('Key_name', 'settings_key_unique');
        $this->assertSame(0, (int) $index->Non_unique);
        $this->assertSame($originalMode, $this->sqlMode());
    }

    private function assertRollbackKeepsDeletedRecords(string $file, string $table, string $index): void
    {
        foreach ([$this->writerDatabase, $this->readerDatabase] as $database) {
            $this->adminConnection->statement('CREATE TABLE `'.$database.'`.`'.$table.'` (
                `id` BIGINT UNSIGNED PRIMARY KEY, `deleted_at` TIMESTAMP NULL,
                INDEX `'.$index.'` (`deleted_at`)
            )');
            if ($table === 'lab_requests') {
                $this->adminConnection->statement('CREATE TABLE `'.$database.'`.`medicine_availabilities` (
                    `id` BIGINT UNSIGNED PRIMARY KEY, `availability_no` VARCHAR(191) NULL
                )');
            }
        }
        DB::table($table)->insert(['id' => 1, 'deleted_at' => '2026-10-08 12:00:00']);

        $rejection = null;
        try {
            $this->migration($file)->down();
        } catch (\RuntimeException $error) {
            $rejection = $error;
        }
        $this->assertInstanceOf(\RuntimeException::class, $rejection, 'Rollback must reject hidden records that have not reached the replica.');
        $this->assertStringContainsString('1 deleted', $rejection->getMessage());

        $column = DB::selectOne('SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$table, 'deleted_at'], false);
        $this->assertSame(1, (int) $column->c);
        $this->assertSame(1, DB::table($table)->useWritePdo()->whereNotNull('deleted_at')->count());
    }

    private function migration(string $file): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/'.$file);
    }

    private function sqlMode(): string
    {
        return DB::selectOne('SELECT @@SESSION.sql_mode AS sql_mode', [], false)->sql_mode;
    }
}
