<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Reads the indexes of one table of the current MySQL schema, for upgrade migrations that must change an index only
 * when it is really there (the clinic's old dump and a fresh install name and keep their indexes differently).
 */
final class LegacyIndexes
{
    public static function isMysql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    /**
     * @return array<string, array{columns: list<string>, non_unique: bool}> index name => its columns, in order
     */
    public static function of(string $table): array
    {
        $indexes = [];
        $rows = DB::select(
            'SELECT INDEX_NAME AS idx, NON_UNIQUE AS non_unique, COLUMN_NAME AS col FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY INDEX_NAME, SEQ_IN_INDEX',
            [$table],
            false
        );
        foreach ($rows as $row) {
            $indexes[$row->idx]['columns'][] = $row->col;
            $indexes[$row->idx]['non_unique'] = (bool) $row->non_unique;
        }

        return $indexes;
    }

    /**
     * Names of the UNIQUE indexes (never the primary key) made of exactly these columns, in this order.
     *
     * @param  array<string, array{columns: list<string>, non_unique: bool}>  $indexes
     * @param  list<string>  $columns
     * @return list<string>
     */
    public static function unique(array $indexes, array $columns): array
    {
        return self::matching($indexes, $columns, false);
    }

    /**
     * Names of the plain (non-unique) indexes made of exactly these columns, in this order.
     *
     * @param  array<string, array{columns: list<string>, non_unique: bool}>  $indexes
     * @param  list<string>  $columns
     * @return list<string>
     */
    public static function plain(array $indexes, array $columns): array
    {
        return self::matching($indexes, $columns, true);
    }

    /**
     * @param  array<string, array{columns: list<string>, non_unique: bool}>  $indexes
     * @param  list<string>  $columns
     * @return list<string>
     */
    private static function matching(array $indexes, array $columns, bool $nonUnique): array
    {
        return array_values(array_keys(array_filter($indexes, fn (array $index, string $name) => $name !== 'PRIMARY'
            && $index['non_unique'] === $nonUnique && $index['columns'] === $columns, ARRAY_FILTER_USE_BOTH)));
    }
}
