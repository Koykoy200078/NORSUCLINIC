<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P2-H2: re-running the seeders used to insert every settings row again. The Settings screen then
 * saved to the FIRST duplicate while the application read the LAST one, so edits looked as if they
 * "did not save". Remove duplicate keys (keeping the oldest row - the one the save path updated) and
 * make the key unique so it cannot happen again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'key')) {
            return;
        }

        $duplicateKeys = DB::table('settings')
            ->select('key')
            ->groupBy('key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('key');

        foreach ($duplicateKeys as $key) {
            $keepId = DB::table('settings')->where('key', $key)->min('id');
            DB::table('settings')->where('key', $key)->where('id', '!=', $keepId)->delete();
        }

        $hasIndex = in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)
            && DB::selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                ['settings', 'settings_key_unique']
            )->c > 0;

        if (! $hasIndex) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unique('key', 'settings_key_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique('settings_key_unique');
            });
        }
    }
};
