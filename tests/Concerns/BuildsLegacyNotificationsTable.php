<?php

namespace Tests\Concerns;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * The clinic's `notifications` table at the stages of its upgrade, for migration regressions that run on a throwaway
 * schema. The test class must also use UsesFixtureDatabase (and call useFixtureDatabase() in setUp()).
 */
trait BuildsLegacyNotificationsTable
{
    /**
     * The clinic's table as the old dump built it (converted to InnoDB) with the foreign key Phase 1 restores. With
     * $explicitUserKey the dump's own `KEY notifications_user_id_foreign (user_id)` is there; without it the key is the
     * one MySQL creates for the foreign key itself (a fresh install).
     */
    protected function notificationsTable(bool $explicitUserKey): void
    {
        DB::statement('CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
        $key = $explicitUserKey ? ', KEY `notifications_user_id_foreign` (`user_id`)' : '';
        DB::statement('CREATE TABLE `notifications` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(191) NULL,
            `type` VARCHAR(191) NULL,
            `read_at` TIMESTAMP NULL,
            `user_id` BIGINT UNSIGNED NULL,
            `created_at` TIMESTAMP NULL,
            `updated_at` TIMESTAMP NULL,
            PRIMARY KEY (`id`)'.$key.'
        ) ENGINE=InnoDB');
        DB::statement('ALTER TABLE `notifications` ADD CONSTRAINT `notifications_user_id_foreign`
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    /** @return array<string, array{columns: list<string>, non_unique: bool}> */
    protected function indexes(): array
    {
        $indexes = [];
        $rows = DB::select(
            'SELECT INDEX_NAME AS idx, NON_UNIQUE AS non_unique, COLUMN_NAME AS col FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY INDEX_NAME, SEQ_IN_INDEX',
            ['notifications'],
            false
        );
        foreach ($rows as $row) {
            $indexes[$row->idx]['columns'][] = $row->col;
            $indexes[$row->idx]['non_unique'] = (bool) $row->non_unique;
        }

        return $indexes;
    }

    /** @return array<string, string> index name => "columns" (+ " unique"), for comparing whole structures */
    protected function indexSummary(): array
    {
        $summary = [];
        foreach ($this->indexes() as $name => $index) {
            $summary[$name] = implode(',', $index['columns']).($index['non_unique'] ? '' : ' unique');
        }
        ksort($summary);

        return $summary;
    }

    protected function hasStandaloneUserIndex(): bool
    {
        foreach ($this->indexes() as $name => $index) {
            if ($name !== 'PRIMARY' && $index['non_unique'] && $index['columns'] === ['user_id']) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> DELETE rule, UPDATE rule of the user foreign key (empty when it does not exist) */
    protected function foreignKeyRules(): array
    {
        $row = DB::selectOne(
            'SELECT DELETE_RULE AS d, UPDATE_RULE AS u FROM information_schema.REFERENTIAL_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            ['notifications', 'notifications_user_id_foreign'],
            false
        );

        return $row === null ? [] : [$row->d, $row->u];
    }

    protected function createStatement(): string
    {
        return (string) array_values((array) DB::selectOne('SHOW CREATE TABLE `notifications`', [], false))[1];
    }

    /**
     * Whether the database refuses a notification for a user that does not exist (a row that is accepted is removed
     * again). $eventKey null is the row MySQL 9.7 skips while a UNIQUE index starting with user_id backs the key.
     */
    protected function orphanIsRefused(?string $eventKey): bool
    {
        try {
            DB::table('notifications')->insert(['user_id' => 999, 'title' => 'orphan', 'event_key' => $eventKey]);
        } catch (QueryException) {
            return true;
        }
        DB::table('notifications')->where('user_id', 999)->delete();

        return false;
    }
}
