<?php

namespace Tests\Feature\Regression;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.1 / 1.2: the clinic's tables are all MyISAM (no foreign keys, no transactions). New tables are always
 * InnoDB whatever the server default is, and the upgrade converts every legacy table.
 */
class LegacyInnoDbConversionTest extends TestCase
{
    use UsesFixtureDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('innodb_conversion');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_new_tables_are_innodb_even_when_the_server_default_is_myisam(): void
    {
        // A WAMP server whose my.ini says default-storage-engine=MyISAM: the clinic's situation.
        DB::statement('SET SESSION default_storage_engine = MyISAM');

        Schema::create('engine_probe', function (Blueprint $table) {
            $table->id();
        });

        $this->assertSame('InnoDB', $this->engineOf('engine_probe'));
    }

    public function test_every_legacy_table_becomes_innodb_and_keeps_its_rows_indexes_and_next_id(): void
    {
        $strictMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `patients` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL,
            `created_at` DATETIME NOT NULL, UNIQUE KEY `patients_name_unique` (`name`), KEY `patients_created_idx` (`created_at`)
        ) ENGINE=MyISAM');
        DB::statement("INSERT INTO `patients` (`id`, `name`, `created_at`) VALUES
            (1, 'Ana', '2026-01-02 03:04:05'), (2, 'Ben', '0000-00-00 00:00:00'), (5, 'Cai', '2027-00-15 00:00:00')");
        DB::statement('ALTER TABLE `patients` AUTO_INCREMENT = 10');
        DB::statement('CREATE TABLE `settings` (`id` INT PRIMARY KEY, `key` VARCHAR(50), `value` TEXT) ENGINE=MyISAM');
        DB::statement("INSERT INTO `settings` VALUES (1, 'clinic_name', 'Clinic')");
        DB::statement('CREATE TABLE `already_innodb` (`id` INT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('INSERT INTO `already_innodb` VALUES (7)');
        DB::statement('CREATE VIEW `patient_names` AS SELECT `id`, `name` FROM `patients`');
        DB::statement('SET SESSION sql_mode = ?', [$strictMode]);
        $rowsBefore = DB::table('patients')->orderBy('id')->get()->all();
        $indexesBefore = $this->indexes('patients');

        $this->loadMigration('2026_09_30_110000_convert_legacy_tables_to_innodb.php')->up();

        foreach (['patients', 'settings', 'already_innodb'] as $table) {
            $this->assertSame('InnoDB', $this->engineOf($table), "{$table} must be InnoDB");
        }
        $this->assertNull($this->engineOf('patient_names'), 'a view has no engine and must stay a view');
        $this->assertCount(3, DB::table('patient_names')->get());
        $this->assertEquals($rowsBefore, DB::table('patients')->orderBy('id')->get()->all(), 'no row may change, zero dates included');
        $this->assertSame($indexesBefore, $this->indexes('patients'));
        $this->assertSame('Clinic', DB::table('settings')->value('value'));
        $this->assertSame(7, (int) DB::table('already_innodb')->value('id'));
        DB::table('patients')->insert(['name' => 'Dee', 'created_at' => '2026-10-09 08:00:00']);
        $this->assertSame(10, (int) DB::table('patients')->where('name', 'Dee')->value('id'), 'the next id must not restart');
        $this->assertSame($strictMode, $this->sqlMode());
    }

    public function test_converting_again_or_on_an_innodb_database_changes_nothing(): void
    {
        DB::statement('CREATE TABLE `legacy` (`id` INT PRIMARY KEY) ENGINE=MyISAM');
        $migration = $this->loadMigration('2026_09_30_110000_convert_legacy_tables_to_innodb.php');

        $migration->up();
        $this->assertSame([], \App\Support\LegacySchemaUpgrade::convertToInnoDb(), 'a second run has nothing left to convert');
        $migration->up();

        $this->assertSame('InnoDB', $this->engineOf('legacy'));
    }

    public function test_a_migration_preview_converts_nothing(): void
    {
        DB::statement('CREATE TABLE `legacy` (`id` INT PRIMARY KEY) ENGINE=MyISAM');
        $originalMode = $this->sqlMode();

        DB::connection()->pretend(fn () => $this->loadMigration('2026_09_30_110000_convert_legacy_tables_to_innodb.php')->up());

        $this->assertSame('MyISAM', $this->engineOf('legacy'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    /**
     * @return list<string> "name:columns:unique" per index of a table, sorted
     */
    private function indexes(string $table): array
    {
        $rows = DB::select(
            'SELECT INDEX_NAME AS name, NON_UNIQUE AS non_unique, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols
             FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? GROUP BY INDEX_NAME, NON_UNIQUE',
            [$table],
            false
        );
        $list = array_map(fn ($r) => "{$r->name}:{$r->cols}:".($r->non_unique ? 'multi' : 'unique'), $rows);
        sort($list);

        return $list;
    }
}
