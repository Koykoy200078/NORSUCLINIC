<?php

namespace Tests\Feature\Regression;

use App\Support\LegacySchemaUpgrade;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.3: the old server stored '0000-00-00' for "no date". Strict MySQL refuses to rewrite such a row, so the
 * upgrade turns every zero (or half-zero) date into NULL where the column allows NULL, and only reports the columns
 * that do not.
 */
class LegacyZeroDatesTest extends TestCase
{
    use UsesFixtureDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('zero_dates');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_zero_dates_become_null_where_the_column_allows_it_and_nothing_else_changes(): void
    {
        $strictMode = $this->visitsWithLegacyDates();

        $report = LegacySchemaUpgrade::normalizeZeroDates();

        $this->assertSame(
            ['visits.born_on' => 2, 'visits.logged_at' => 1, 'visits.seen_at' => 2],
            $this->sorted($report['fixed'])
        );
        $this->assertSame(['visits.required_at' => 1], $report['blocked'], 'a NOT NULL column cannot hold NULL: it is only reported');

        $rows = DB::table('visits')->orderBy('id')->get()->keyBy('id');
        $this->assertSame('2026-01-02 03:04:05', $rows[1]->seen_at, 'a valid date is never touched');
        $this->assertSame('2000-01-02', $rows[1]->born_on);
        $this->assertNull($rows[2]->seen_at);
        $this->assertNull($rows[2]->born_on);
        $this->assertNull($rows[2]->logged_at);
        $this->assertSame('0000-00-00 00:00:00', $rows[2]->required_at);
        $this->assertNull($rows[3]->seen_at, 'a date with a zero month is as unusable as a zero date');
        $this->assertNull($rows[3]->born_on, 'a date with a zero day too');
        $this->assertSame('2026-01-02 03:04:05', $rows[3]->logged_at);
        $this->assertNull($rows[4]->seen_at);
        $this->assertSame(['valid', 'all zero', 'partial', 'nulls'], $rows->pluck('note')->values()->all());
        $this->assertSame(4, DB::table('visits')->count());
        $this->assertSame($strictMode, $this->sqlMode());
    }

    public function test_a_second_run_finds_nothing_to_fix(): void
    {
        $this->visitsWithLegacyDates();
        LegacySchemaUpgrade::normalizeZeroDates();

        $second = LegacySchemaUpgrade::normalizeZeroDates();

        $this->assertSame([], $second['fixed']);
        $this->assertSame(['visits.required_at' => 1], $second['blocked'], 'the blocked column is still reported until someone fixes it');
    }

    public function test_the_migration_runs_the_normalization_and_restores_the_sql_mode(): void
    {
        $strictMode = $this->visitsWithLegacyDates();

        $this->loadMigration('2026_09_30_121500_normalize_zero_dates.php')->up();

        $this->assertNull(DB::table('visits')->where('id', 2)->value('seen_at'));
        $this->assertSame($strictMode, $this->sqlMode());
    }

    /**
     * @return string the strict sql_mode the application runs with
     */
    private function visitsWithLegacyDates(): string
    {
        $strictMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `visits` (
            `id` INT PRIMARY KEY, `seen_at` DATETIME NULL, `born_on` DATE NULL, `logged_at` TIMESTAMP NULL DEFAULT NULL,
            `required_at` DATETIME NOT NULL, `note` VARCHAR(20) NULL
        ) ENGINE=InnoDB');
        DB::statement("INSERT INTO `visits` VALUES
            (1, '2026-01-02 03:04:05', '2000-01-02', '2026-01-02 03:04:05', '2026-01-02 03:04:05', 'valid'),
            (2, '0000-00-00 00:00:00', '0000-00-00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'all zero'),
            (3, '2027-00-15 00:00:00', '2027-05-00', '2026-01-02 03:04:05', '2026-01-02 03:04:05', 'partial'),
            (4, NULL, NULL, NULL, '2026-01-02 03:04:05', 'nulls')");
        // A view repeats the columns in information_schema; it must not be processed a second time.
        DB::statement('CREATE VIEW `visit_view` AS SELECT * FROM `visits`');
        DB::statement('SET SESSION sql_mode = ?', [$strictMode]);

        return $strictMode;
    }

    /**
     * @param  array<string, int>  $map
     * @return array<string, int>
     */
    private function sorted(array $map): array
    {
        ksort($map);

        return $map;
    }
}
