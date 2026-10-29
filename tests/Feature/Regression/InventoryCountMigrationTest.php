<?php

namespace Tests\Feature\Regression;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * The count-sheet tables (2026-10-29) on a throwaway schema that has only the two tables they point at: made once, a second
 * run changes nothing, they are InnoDB with their five keys, and a rollback never throws counts away.
 * (A throwaway schema because CREATE TABLE commits: on the shared test schema it would leave tables behind.)
 */
class InventoryCountMigrationTest extends TestCase
{
    use UsesFixtureDatabase;

    private const FILE = '2026_10_29_150000_create_inventory_counts_tables.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->useFixtureDatabase('inventory_counts');
        DB::statement('CREATE TABLE users (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE medicines (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    private function keys(): array
    {
        return collect(DB::select(
            "SELECT TABLE_NAME t, CONSTRAINT_NAME n FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = 'FOREIGN KEY' ORDER BY n"
        ))->map(fn ($row) => $row->t . '.' . $row->n)->all();
    }

    public function test_it_makes_both_tables_as_innodb_with_their_five_keys_and_a_second_run_changes_nothing(): void
    {
        $migration = $this->loadMigration(self::FILE);
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasTable('inventory_counts') && Schema::hasTable('inventory_count_lines'));
        $engines = collect(DB::select("SELECT TABLE_NAME t, ENGINE e FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'inventory_count%'"))->pluck('e', 't')->all();
        $this->assertSame(['inventory_count_lines' => 'InnoDB', 'inventory_counts' => 'InnoDB'], $engines);
        $this->assertSame([
            'inventory_count_lines.inventory_count_lines_adjusted_by_foreign',
            'inventory_count_lines.inventory_count_lines_inventory_count_id_foreign',
            'inventory_count_lines.inventory_count_lines_medicine_id_foreign',
            'inventory_counts.inventory_counts_created_by_foreign',
            'inventory_counts.inventory_counts_finalized_by_foreign',
        ], $this->keys());
        $this->assertSame(1, (int) DB::selectOne("SELECT COUNT(DISTINCT INDEX_NAME) c FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventory_count_lines' AND INDEX_NAME = 'uq_inventory_count_lines'")->c);
    }

    public function test_rolling_back_drops_empty_tables_but_refuses_to_throw_counts_away(): void
    {
        $migration = $this->loadMigration(self::FILE);
        $migration->up();
        DB::table('inventory_counts')->insert(['year' => 2026, 'quarter' => 1, 'scope' => 'all', 'counted_on' => '2026-03-31', 'status' => 'draft']);

        try {
            $migration->down();
            $this->fail('the rollback threw a count sheet away');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('count sheet', $e->getMessage());
        }
        $this->assertSame(1, DB::table('inventory_counts')->count());

        DB::table('inventory_counts')->delete();
        $migration->down();
        $this->assertFalse(Schema::hasTable('inventory_counts') || Schema::hasTable('inventory_count_lines'));
    }
}
