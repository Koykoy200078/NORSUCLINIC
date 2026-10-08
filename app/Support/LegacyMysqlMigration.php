<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class LegacyMysqlMigration
{
    /** Allow table rebuilds to retain old zero dates without weakening other strict checks. */
    public static function withoutZeroDateChecks(callable $callback): void
    {
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $callback();

            return;
        }

        // Read the writer's mode; SELECT normally uses the read connection.
        // In migrate --pretend, SELECT has no result and statements are only logged.
        $sqlMode = $connection->selectOne('SELECT @@SESSION.sql_mode AS sql_mode', [], false)?->sql_mode ?? '';
        $legacySqlMode = implode(',', array_diff(explode(',', $sqlMode), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']));

        try {
            $connection->statement('SET SESSION sql_mode = ?', [$legacySqlMode]);
            $callback();
        } finally {
            // Later migrations and application writes must retain the original mode.
            $connection->statement('SET SESSION sql_mode = ?', [$sqlMode]);
        }
    }
}
