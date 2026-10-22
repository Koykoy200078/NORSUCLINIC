<?php

use App\Support\LegacyIndexes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The alert migration (2026_10_21_050000) made the alert pair unique as `(user_id, event_key)`. That index starts with
 * the foreign key's column, so MySQL binds the `notifications.user_id` foreign key to it, and on MySQL 9.7.1 a row whose
 * event_key is NULL is then no longer checked against `users` (rows with a key and ON DELETE CASCADE still work). The
 * same pair in the other order, `(event_key, user_id)`, is just as unique but cannot back the foreign key, so a plain
 * `user_id` index does and the key stays fully enforced.
 *
 * This swaps the index and makes sure that plain `user_id` index exists first (a fresh install loses the one MySQL
 * made for the foreign key when the old unique index covered it; the clinic copy keeps the dump's own). Every path ends
 * in the same structure, no row changes, and a copy that never had the alert index is left alone.
 */
return new class extends Migration
{
    private const OLD_PAIR = ['user_id', 'event_key'];

    private const NEW_PAIR = ['event_key', 'user_id'];

    public function up(): void
    {
        if (! $this->applies()) {
            return;
        }
        $indexes = LegacyIndexes::of('notifications');
        $old = LegacyIndexes::unique($indexes, self::OLD_PAIR);
        $new = LegacyIndexes::unique($indexes, self::NEW_PAIR);
        if ($old === [] && $new === []) {
            return;
        }

        // The foreign key needs an index at every moment: the plain one goes in before the old unique one goes out.
        if (LegacyIndexes::plain($indexes, ['user_id']) === []) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index('user_id', 'notifications_user_id_foreign');
            });
        }
        foreach ($old as $name) {
            Schema::table('notifications', function (Blueprint $table) use ($name) {
                $table->dropUnique($name);
            });
        }
        if ($new === []) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unique(self::NEW_PAIR);
            });
        }
    }

    /** Back to the alert migration's own index, so that migration can be rolled back next. */
    public function down(): void
    {
        if (! $this->applies()) {
            return;
        }
        $indexes = LegacyIndexes::of('notifications');
        if (LegacyIndexes::unique($indexes, self::OLD_PAIR) === []) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unique(self::OLD_PAIR);
            });
        }
        foreach (LegacyIndexes::unique($indexes, self::NEW_PAIR) as $name) {
            Schema::table('notifications', function (Blueprint $table) use ($name) {
                $table->dropUnique($name);
            });
        }
    }

    private function applies(): bool
    {
        return LegacyIndexes::isMysql() && Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'event_key');
    }
};
