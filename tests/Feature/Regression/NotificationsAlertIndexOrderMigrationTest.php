<?php

namespace Tests\Feature\Regression;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsLegacyNotificationsTable;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Found while fixing the clinic's redundant notifications key (NotificationsLegacyIndexMigrationTest): the alert
 * migration's `unique (user_id, event_key)` starts with the foreign key's column. On MySQL 9.7.1 the foreign key is then
 * bound to that UNIQUE index and a row whose event_key is NULL is no longer checked against `users` (rows with a key,
 * and ON DELETE CASCADE, still work). The same unique pair in the other order, `(event_key, user_id)`, cannot back the
 * foreign key, so a plain `user_id` index keeps it fully enforced.
 *
 * Every path (the clinic's copy with the dump's explicit key, a fresh install, and a copy that already ran the drop
 * migration) must end in the same structure: PRIMARY, a plain user_id index, and the unique (event_key, user_id).
 */
class NotificationsAlertIndexOrderMigrationTest extends TestCase
{
    use BuildsLegacyNotificationsTable;
    use UsesFixtureDatabase;

    private const ALERT_FIELDS = '2026_10_21_050000_add_clinic_alert_fields_to_notifications.php';

    private const REORDER = '2026_10_22_080000_reorder_notifications_alert_unique_index.php';

    private const DROP = '2026_10_22_090000_drop_redundant_notifications_user_index.php';

    private const FINAL_STRUCTURE = [
        'PRIMARY' => 'id unique',
        'notifications_event_key_user_id_unique' => 'event_key,user_id unique',
        'notifications_user_id_foreign' => 'user_id',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('notif_order');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    /** @return array<string, array{0: bool, 1: list<string>}> */
    public static function pathsToTheSameStructure(): array
    {
        return [
            'clinic copy, reorder then drop' => [true, [self::REORDER, self::DROP]],
            'clinic copy, drop then reorder' => [true, [self::DROP, self::REORDER]],
            'fresh install, reorder then drop' => [false, [self::REORDER, self::DROP]],
            'fresh install, drop then reorder' => [false, [self::DROP, self::REORDER]],
            'clinic copy, reorder only' => [true, [self::REORDER]],
            'fresh install, reorder only' => [false, [self::REORDER]],
        ];
    }

    /**
     * @param  list<string>  $migrations
     */
    #[DataProvider('pathsToTheSameStructure')]
    public function test_every_path_ends_with_the_same_structure_and_a_fully_enforced_foreign_key(bool $explicitUserKey, array $migrations): void
    {
        $this->notificationsTable($explicitUserKey);
        $this->loadMigration(self::ALERT_FIELDS)->up();

        foreach ($migrations as $file) {
            $this->loadMigration($file)->up();
        }

        $this->assertSame(self::FINAL_STRUCTURE, $this->indexSummary());
        $this->assertSame(['CASCADE', 'CASCADE'], $this->foreignKeyRules());
        $this->assertTrue($this->orphanIsRefused('low:1'), 'an alert with a key for a user that does not exist is refused');
        $this->assertTrue($this->orphanIsRefused(null), 'and so is one without a key (the row MySQL skipped under the old index)');
    }

    public function test_no_row_is_changed_and_the_cascade_still_removes_every_alert_of_a_user(): void
    {
        $this->notificationsTable(explicitUserKey: true);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        DB::table('notifications')->insert([
            ['user_id' => 1, 'title' => 'Low stock: Laradin', 'event_key' => 'low:1'],
            ['user_id' => 1, 'title' => 'a resolved alert', 'event_key' => null],
            ['user_id' => 1, 'title' => 'a legacy notification', 'event_key' => null],
            ['user_id' => 2, 'title' => 'someone else', 'event_key' => 'low:1'],
        ]);
        $before = DB::table('notifications')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();

        $this->loadMigration(self::REORDER)->up();

        $this->assertSame($before, DB::table('notifications')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all());

        DB::table('users')->where('id', 1)->delete();
        $this->assertSame(['someone else'], DB::table('notifications')->pluck('title')->all());
    }

    public function test_the_alert_pair_stays_unique(): void
    {
        $this->notificationsTable(explicitUserKey: false);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        $this->loadMigration(self::REORDER)->up();
        DB::table('users')->insert(['id' => 1]);
        DB::table('notifications')->insert(['user_id' => 1, 'title' => 'a', 'event_key' => 'low:1']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('notifications')->insert(['user_id' => 1, 'title' => 'b', 'event_key' => 'low:1']);
    }

    public function test_the_old_alert_index_is_found_by_its_columns_not_by_its_name(): void
    {
        $this->notificationsTable(explicitUserKey: true);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        DB::statement('ALTER TABLE `notifications` RENAME INDEX `notifications_user_id_event_key_unique` TO `an_older_name`');

        $this->loadMigration(self::REORDER)->up();

        $this->assertSame(self::FINAL_STRUCTURE, $this->indexSummary());
    }

    public function test_a_second_run_changes_nothing(): void
    {
        $this->notificationsTable(explicitUserKey: false);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        $this->loadMigration(self::REORDER)->up();
        $after = $this->createStatement();

        $this->loadMigration(self::REORDER)->up();

        $this->assertSame($after, $this->createStatement());
    }

    public function test_a_copy_without_the_alert_index_is_left_alone(): void
    {
        $this->notificationsTable(explicitUserKey: true);
        $before = $this->createStatement();

        $this->loadMigration(self::REORDER)->up();

        $this->assertSame($before, $this->createStatement());
        $this->assertFalse(Schema::hasColumn('notifications', 'event_key'));
    }

    public function test_a_database_without_the_table_is_not_an_error(): void
    {
        $this->loadMigration(self::REORDER)->up();

        $this->assertFalse(Schema::hasTable('notifications'));
    }

    public function test_the_whole_chain_can_be_rolled_back_in_reverse_order(): void
    {
        $this->notificationsTable(explicitUserKey: false);
        $alert = $this->loadMigration(self::ALERT_FIELDS);
        $reorder = $this->loadMigration(self::REORDER);
        $drop = $this->loadMigration(self::DROP);
        $alert->up();
        $reorder->up();
        $drop->up();

        $drop->down();
        $reorder->down();
        $this->assertSame('user_id,event_key unique', $this->indexSummary()['notifications_user_id_event_key_unique'], 'rolling back the reorder restores the alert migration\'s own index');
        $this->assertArrayNotHasKey('notifications_event_key_user_id_unique', $this->indexSummary());
        $alert->down();

        $this->assertFalse(Schema::hasColumn('notifications', 'event_key'));
        $this->assertSame(['CASCADE', 'CASCADE'], $this->foreignKeyRules());
    }
}
