<?php

namespace Tests\Feature\Regression;

use App\Support\LegacySchemaUpgrade;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.4: the clinic's tables have none of the foreign keys a fresh install has. The upgrade adds each missing
 * key from ForeignKeyCatalog, but only when it can do so without touching a single row: both tables InnoDB, the two
 * columns of the same type, and no row pointing at a parent that does not exist. Anything else is skipped and said.
 */
class LegacyForeignKeysTest extends TestCase
{
    use UsesFixtureDatabase;

    private const KEY = 'doctors_user_id_foreign';

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('legacy_fks');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_a_missing_key_is_added_with_the_catalog_rules(): void
    {
        $this->usersAndDoctors();
        DB::table('users')->insert(['id' => 1]);
        DB::table('doctors')->insert(['id' => 1, 'user_id' => 1]);

        $report = LegacySchemaUpgrade::restoreForeignKeys();

        $this->assertSame([self::KEY], $report['added']);
        $this->assertSame(['CASCADE', 'CASCADE'], $this->rules(self::KEY), 'doctors.user_id is ON DELETE CASCADE ON UPDATE CASCADE');
        $this->assertSame(1, DB::table('doctors')->count());
        $this->assertArrayHasKey('addresses_city_id_foreign', $report['skipped'], 'a key whose tables do not exist yet is skipped, not an error');
    }

    public function test_a_second_run_finds_the_key_already_there(): void
    {
        $this->usersAndDoctors();
        LegacySchemaUpgrade::restoreForeignKeys();

        $second = LegacySchemaUpgrade::restoreForeignKeys();

        $this->assertSame([], $second['added']);
        $this->assertContains(self::KEY, $second['present']);
        $this->assertSame(1, $this->foreignKeyCount('doctors'));
    }

    public function test_a_key_that_exists_under_another_name_is_not_added_twice(): void
    {
        $this->usersAndDoctors();
        DB::statement('ALTER TABLE `doctors` ADD CONSTRAINT `my_old_name` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)');

        $report = LegacySchemaUpgrade::restoreForeignKeys();

        $this->assertSame([], $report['added']);
        $this->assertContains(self::KEY, $report['present']);
        $this->assertSame(1, $this->foreignKeyCount('doctors'));
    }

    public function test_rows_pointing_at_a_missing_parent_block_the_key_and_are_left_untouched(): void
    {
        $this->usersAndDoctors();
        DB::table('users')->insert(['id' => 1]);
        DB::table('doctors')->insert([['id' => 1, 'user_id' => 1], ['id' => 2, 'user_id' => 99], ['id' => 3, 'user_id' => 99]]);

        $report = LegacySchemaUpgrade::restoreForeignKeys();

        $this->assertSame([], $report['added']);
        $this->assertStringContainsString('2 row(s)', $report['skipped'][self::KEY]);
        $this->assertStringContainsString('99', $report['skipped'][self::KEY], 'the orphan value is named so it can be fixed by hand');
        $this->assertSame(0, $this->foreignKeyCount('doctors'));
        $this->assertSame([1, 99, 99], DB::table('doctors')->orderBy('id')->pluck('user_id')->map(fn ($v) => (int) $v)->all(), 'no row is changed or deleted');
    }

    public function test_a_null_column_is_not_an_orphan(): void
    {
        $this->usersAndDoctors();
        DB::statement('ALTER TABLE `doctors` MODIFY `user_id` BIGINT UNSIGNED NULL');
        DB::table('doctors')->insert(['id' => 1, 'user_id' => null]);

        $this->assertSame([self::KEY], LegacySchemaUpgrade::restoreForeignKeys()['added']);
    }

    public function test_columns_of_different_types_cannot_be_linked(): void
    {
        $this->usersAndDoctors();
        DB::statement('ALTER TABLE `doctors` MODIFY `user_id` INT NOT NULL');

        $report = LegacySchemaUpgrade::restoreForeignKeys();

        $this->assertSame([], $report['added']);
        $this->assertStringContainsString('type', $report['skipped'][self::KEY]);
    }

    public function test_a_myisam_table_cannot_carry_a_key(): void
    {
        $this->usersAndDoctors();
        DB::statement('ALTER TABLE `doctors` ENGINE = MyISAM');

        $report = LegacySchemaUpgrade::restoreForeignKeys();

        $this->assertSame([], $report['added']);
        $this->assertStringContainsString('MyISAM', $report['skipped'][self::KEY]);
    }

    public function test_adding_a_key_keeps_old_zero_dates_in_the_same_table(): void
    {
        $strictMode = $this->allowFixtureZeroDates();
        $this->usersAndDoctors();
        DB::statement('ALTER TABLE `doctors` ADD COLUMN `created_at` DATETIME NOT NULL');
        DB::statement("INSERT INTO `users` (`id`) VALUES (1)");
        DB::statement("INSERT INTO `doctors` (`id`, `user_id`, `created_at`) VALUES (1, 1, '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$strictMode]);

        $report = LegacySchemaUpgrade::restoreForeignKeys();

        $this->assertSame([self::KEY], $report['added']);
        $this->assertSame('0000-00-00 00:00:00', DB::table('doctors')->value('created_at'));
        $this->assertSame($strictMode, $this->sqlMode());
    }

    public function test_the_migration_restores_the_keys(): void
    {
        $this->usersAndDoctors();

        $this->loadMigration('2026_10_08_120000_restore_missing_foreign_keys.php')->up();

        $this->assertSame(1, $this->foreignKeyCount('doctors'));
    }

    private function usersAndDoctors(): void
    {
        DB::statement('CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `doctors` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL
        ) ENGINE=InnoDB');
    }

    /**
     * @return array{0: string, 1: string} delete rule, update rule
     */
    private function rules(string $constraint): array
    {
        $row = DB::selectOne(
            'SELECT DELETE_RULE AS d, UPDATE_RULE AS u FROM information_schema.REFERENTIAL_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ?',
            [$constraint],
            false
        );

        return [$row->d, $row->u];
    }

    private function foreignKeyCount(string $table): int
    {
        return (int) DB::selectOne(
            "SELECT COUNT(*) AS n FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table],
            false
        )->n;
    }
}
