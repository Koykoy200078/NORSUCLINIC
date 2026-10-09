<?php

namespace Tests\Feature\Regression;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.2a, found by rehearsal R0: on a server whose default engine is InnoDB the illness / services migration
 * stopped after three tables (a foreign key cannot point at a MyISAM `document_issuances`), and a plain retry then
 * failed with "table already exists". The migration must be able to finish a database left in that state.
 */
class IllnessTablesMigrationRerunTest extends TestCase
{
    use UsesFixtureDatabase;

    private const FILE = '2026_10_02_090000_create_illness_and_service_tables.php';

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('illness_rerun');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_a_database_left_half_built_by_a_failed_attempt_can_finish_the_migration(): void
    {
        $migration = $this->loadMigration(self::FILE);

        // The failed attempt: no usable document_issuances, so the foreign key fails after three tables exist.
        try {
            $migration->up();
            $this->fail('The foreign key to a missing document_issuances table must fail, as it did on the clinic dump.');
        } catch (QueryException) {
            // expected: this is the half-built state
        }
        foreach (['illness_systems', 'illnesses', 'consultation_illnesses'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} is left behind by the failed attempt");
        }
        $this->assertFalse(Schema::hasTable('service_types'));

        DB::statement('CREATE TABLE `document_issuances` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');

        $migration->up();

        foreach (['illness_systems', 'illnesses', 'consultation_illnesses', 'service_types', 'consultation_services'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} must exist after the retry");
        }
        $illnesses = DB::table('illnesses')->count();
        $services = DB::table('service_types')->count();
        $this->assertGreaterThan(0, $illnesses);
        $this->assertGreaterThan(0, $services);

        // Running it once more must not duplicate the seeded lists.
        $migration->up();
        $this->assertSame($illnesses, DB::table('illnesses')->count());
        $this->assertSame($services, DB::table('service_types')->count());
    }
}
