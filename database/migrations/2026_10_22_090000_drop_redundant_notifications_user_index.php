<?php

use App\Support\LegacyIndexes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CLINIC-DATA UPGRADE (found by the 2026-10-22 rehearsal of the real dump, plan 8.5 / Phase 9.2).
 *
 * The clinic's `notifications` table carries an explicit key `notifications_user_id_foreign (user_id)` from the old
 * dump. The alert migration (2026_10_21_050000) adds `unique (user_id, event_key)`. On a fresh install MySQL then drops
 * the index it created implicitly for the foreign key, but it never drops an explicit one, so the upgraded clinic kept
 * a redundant index and `db:integrity --compare-fresh` failed ("index (user_id) is not in a fresh install").
 *
 * This removes only that redundant single-column index, and only while another index starting with `user_id` can
 * back the foreign key. A fresh install (nothing to drop) and a copy without the alert index (nothing else backs the
 * key) are left untouched. No row changes.
 *
 * Since 2026_10_22_080000 swaps the alert index to (event_key, user_id) -- which cannot back the foreign key -- the
 * plain user_id key is no longer redundant there, so in the normal chain this finds nothing to drop. It stays for a
 * copy that still has the old alert index, and it is safe in either order with the swap.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->redundantUserIndexes() as $name) {
            Schema::table('notifications', function (Blueprint $table) use ($name) {
                $table->dropIndex($name);
            });
        }
    }

    /** Restores the legacy key so the alert migration can be rolled back too (its unique index backs the foreign key). */
    public function down(): void
    {
        if (! LegacyIndexes::isMysql() || ! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'user_id')) {
            return;
        }
        if (LegacyIndexes::plain(LegacyIndexes::of('notifications'), ['user_id']) === []) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index('user_id', 'notifications_user_id_foreign');
            });
        }
    }

    /** @return list<string> */
    private function redundantUserIndexes(): array
    {
        if (! LegacyIndexes::isMysql() || ! Schema::hasTable('notifications')) {
            return [];
        }
        $indexes = LegacyIndexes::of('notifications');
        $standalone = LegacyIndexes::plain($indexes, ['user_id']);
        $covering = array_filter($indexes, fn (array $index, string $name) => $name !== 'PRIMARY'
            && ! in_array($name, $standalone, true) && $index['columns'][0] === 'user_id', ARRAY_FILTER_USE_BOTH);

        return $covering === [] ? [] : $standalone;
    }
};
