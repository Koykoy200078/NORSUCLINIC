<?php

namespace Tests\Feature\Regression;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsLegacyNotificationsTable;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Found by the 2026-10-22 rehearsal of the real clinic dump (plan 8.5 / Phase 9.2): the clinic's `notifications` table
 * carries an EXPLICIT key `notifications_user_id_foreign (user_id)` from the old MyISAM dump. The alert migration adds
 * a `(user_id, event_key)` unique index; on a fresh install MySQL then silently drops the index it had created for the
 * foreign key, but it never drops an explicit one. The upgraded clinic therefore kept a redundant index and
 * `db:integrity --compare-fresh` reported "index (user_id) is not in a fresh install" (exit code 1).
 *
 * This migration removes that one redundant index, and only when another index can back the foreign key. Since the
 * alert index is reordered to (event_key, user_id) by 2026_10_22_080000, it no longer fires in the normal chain; it
 * still protects a copy that has the old alert index (see NotificationsAlertIndexOrderMigrationTest for the chain).
 */
class NotificationsLegacyIndexMigrationTest extends TestCase
{
    use BuildsLegacyNotificationsTable;
    use UsesFixtureDatabase;

    private const ALERT_FIELDS = '2026_10_21_050000_add_clinic_alert_fields_to_notifications.php';

    private const FIX = '2026_10_22_090000_drop_redundant_notifications_user_index.php';

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('notif_index');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_the_clinics_explicit_user_index_is_dropped_once_the_alert_index_covers_it(): void
    {
        $this->notificationsTable(explicitUserKey: true);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        $this->assertTrue($this->hasStandaloneUserIndex(), 'precondition: the upgraded clinic copy keeps the explicit index (the defect)');

        $this->loadMigration(self::FIX)->up();

        $this->assertFalse($this->hasStandaloneUserIndex(), 'the redundant single-column index is gone, like on a fresh install');
        $this->assertSame(['user_id', 'event_key'], $this->indexes()['notifications_user_id_event_key_unique']['columns']);
        $this->assertFalse($this->indexes()['notifications_user_id_event_key_unique']['non_unique'], 'the alert index stays unique');
        $this->assertSame(['CASCADE', 'CASCADE'], $this->foreignKeyRules(), 'the foreign key is still there, with its rules');
    }

    public function test_the_foreign_key_still_protects_alerts_and_cascades_after_the_index_is_dropped(): void
    {
        $this->notificationsTable(explicitUserKey: true);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        $this->loadMigration(self::FIX)->up();

        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        DB::table('notifications')->insert([
            ['user_id' => 1, 'title' => 'Low stock: Laradin', 'event_key' => 'low:1'],
            ['user_id' => 1, 'title' => 'a resolved alert', 'event_key' => null],
            ['user_id' => 2, 'title' => 'someone else', 'event_key' => 'low:1'],
        ]);

        // An alert always carries its event key. (On MySQL 9.7 a row with a NULL event key is not checked while a UNIQUE
        // index that starts with user_id backs the key; the reorder migration 2026_10_22_080000 removes that.)
        $this->assertTrue($this->orphanIsRefused('low:1'), 'An alert for a user that does not exist must still be refused.');

        DB::table('users')->where('id', 1)->delete();
        $this->assertSame(['someone else'], DB::table('notifications')->pluck('title')->all(), 'ON DELETE CASCADE still removes all of the user\'s alerts, resolved ones too');
    }

    public function test_a_fresh_install_shaped_table_is_left_exactly_as_it_is(): void
    {
        $this->notificationsTable(explicitUserKey: false);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        $this->assertFalse($this->hasStandaloneUserIndex(), 'precondition: MySQL already dropped the key it made for the foreign key');
        $before = $this->createStatement();

        $this->loadMigration(self::FIX)->up();

        $this->assertSame($before, $this->createStatement());
    }

    public function test_a_second_run_changes_nothing(): void
    {
        $this->notificationsTable(explicitUserKey: true);
        $this->loadMigration(self::ALERT_FIELDS)->up();
        $this->loadMigration(self::FIX)->up();
        $after = $this->createStatement();

        $this->loadMigration(self::FIX)->up();

        $this->assertSame($after, $this->createStatement());
    }

    public function test_the_index_stays_while_nothing_else_can_back_the_foreign_key(): void
    {
        // A copy that has not had the alert migration yet: the explicit key is the only index on user_id.
        $this->notificationsTable(explicitUserKey: true);

        $this->loadMigration(self::FIX)->up();

        $this->assertTrue($this->hasStandaloneUserIndex(), 'dropping it would make MySQL refuse (error 1553) or leave the key unbacked');
        $this->assertSame(['CASCADE', 'CASCADE'], $this->foreignKeyRules());
    }

    public function test_a_database_without_the_table_is_not_an_error(): void
    {
        $this->assertFalse(Schema::hasTable('notifications'));

        $this->loadMigration(self::FIX)->up();

        $this->assertFalse(Schema::hasTable('notifications'));
    }

    public function test_rolling_back_restores_the_index_so_the_alert_migration_can_be_rolled_back_too(): void
    {
        $this->notificationsTable(explicitUserKey: false);
        $alert = $this->loadMigration(self::ALERT_FIELDS);
        $alert->up();
        $fix = $this->loadMigration(self::FIX);
        $fix->up();

        $fix->down();
        $alert->down();

        $this->assertFalse(Schema::hasColumn('notifications', 'event_key'), 'the alert columns can still be removed');
        $this->assertSame(['CASCADE', 'CASCADE'], $this->foreignKeyRules());
    }
}
