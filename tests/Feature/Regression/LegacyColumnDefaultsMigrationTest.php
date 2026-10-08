<?php

namespace Tests\Feature\Regression;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyColumnDefaultsMigrationTest extends TestCase
{
    private ?Connection $adminConnection = null;
    private ?string $fixtureDatabase = null;
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();
        $this->adminConnection = DB::connection();
        if ($this->adminConnection->getDriverName() !== 'mysql') {
            $this->markTestSkipped('This regression requires MySQL/MariaDB.');
        }

        // Never alter the application's tables or the shared test-suite schema.
        $database = 'legacy_defaults_'.bin2hex(random_bytes(6)).'_test';
        $this->adminConnection->statement('CREATE DATABASE `'.$database.'`');
        $this->fixtureDatabase = $database;
        config(['database.connections.legacy_defaults_fixture' => array_merge(
            $this->adminConnection->getConfig(),
            ['database' => $database, 'url' => null, 'prefix' => '']
        )]);
        DB::setDefaultConnection('legacy_defaults_fixture');
    }

    protected function tearDown(): void
    {
        try {
            if ($this->fixtureDatabase !== null) {
                DB::purge('legacy_defaults_fixture');
                DB::setDefaultConnection($this->originalConnection);
                $this->adminConnection->statement('DROP DATABASE `'.$this->fixtureDatabase.'`');
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_existing_zero_dates_survive_the_upgrade_and_a_retry_in_strict_mode(): void
    {
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
        DB::statement('CREATE TABLE `medicines` (
            `id` INT PRIMARY KEY, `salt_composition` VARCHAR(191) NOT NULL,
            `quantity` INT NOT NULL, `available_quantity` INT NOT NULL,
            `created_at` DATETIME NOT NULL
        )');
        DB::statement("INSERT INTO `medicines` VALUES (1, 'Original composition', 9, 7, '0000-00-00 00:00:00')");
        DB::statement('CREATE TABLE `sale_medicines` (
            `id` INT PRIMARY KEY, `expiry_date` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL, `sale_quantity` INT NOT NULL
        ) ENGINE=MyISAM'); // Force row copying, as older servers also do for this ALTER.
        DB::statement("INSERT INTO `sale_medicines` VALUES
            (1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 2),
            (2, '2027-00-15 00:00:00', '2026-10-01 12:00:00', 3),
            (3, '2028-02-29 13:14:15', '2026-10-01 12:00:00', 4)");
        // Simulate an earlier attempt that already committed the first ALTER.
        DB::statement('ALTER TABLE `medicines` MODIFY `salt_composition` VARCHAR(191) NULL');
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
        $originalMode = $this->sqlMode();
        $medicineBefore = DB::table('medicines')->first();
        $salesBefore = DB::table('sale_medicines')->orderBy('id')->get()->all();

        $migration = $this->migration();
        $migration->up();
        $migration->up();

        $this->assertSame($originalMode, $this->sqlMode());
        $this->assertEquals($medicineBefore, DB::table('medicines')->first());
        $this->assertEquals($salesBefore, DB::table('sale_medicines')->orderBy('id')->get()->all());
        $this->assertSame('YES', $this->column('sale_medicines', 'expiry_date')->is_nullable);
        $this->assertSame('0', (string) $this->column('medicines', 'quantity')->column_default);
        $this->assertSame('0', (string) $this->column('medicines', 'available_quantity')->column_default);

        DB::table('sale_medicines')->insert([
            'id' => 4, 'created_at' => '2026-10-08 12:00:00', 'sale_quantity' => 1,
        ]);
        $this->assertNull(DB::table('sale_medicines')->where('id', 4)->value('expiry_date'));
    }

    public function test_a_failed_alter_restores_sql_mode_and_keeps_other_strict_checks(): void
    {
        DB::statement('CREATE TABLE `medicines` (
            `id` INT PRIMARY KEY, `salt_composition` VARCHAR(191) NOT NULL,
            `quantity` VARCHAR(191) NOT NULL
        )');
        DB::statement("INSERT INTO `medicines` VALUES (1, 'Original composition', 'cannot-convert-to-int')");
        $originalMode = $this->sqlMode();

        try {
            $this->migration()->up();
            $this->fail('An invalid quantity must still fail instead of being silently coerced to zero.');
        } catch (QueryException $error) {
            $this->assertStringContainsString('quantity', $error->getSql());
        }

        $this->assertSame($originalMode, $this->sqlMode());
        $this->assertSame('cannot-convert-to-int', DB::table('medicines')->value('quantity'));
    }

    public function test_missing_legacy_tables_and_columns_are_skipped(): void
    {
        DB::statement('CREATE TABLE `medicines` (`id` INT PRIMARY KEY, `quantity` INT NOT NULL)');
        $originalMode = $this->sqlMode();

        $this->migration()->up();

        DB::table('medicines')->insert(['id' => 1]);
        $this->assertSame(0, (int) DB::table('medicines')->value('quantity'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_a_migration_preview_leaves_schema_and_sql_mode_unchanged(): void
    {
        DB::statement('CREATE TABLE `medicines` (`id` INT PRIMARY KEY, `quantity` INT NOT NULL)');
        $originalMode = $this->sqlMode();

        DB::connection()->pretend(fn () => $this->migration()->up());

        $this->assertNull($this->column('medicines', 'quantity')->column_default);
        $this->assertSame($originalMode, $this->sqlMode());
    }

    private function migration(): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/2026_09_30_121000_give_legacy_not_null_columns_defaults.php');
    }

    private function sqlMode(): string
    {
        return DB::selectOne('SELECT @@SESSION.sql_mode AS sql_mode', [], false)->sql_mode;
    }

    private function column(string $table, string $column): object
    {
        return DB::selectOne('SELECT IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$table, $column]);
    }
}
