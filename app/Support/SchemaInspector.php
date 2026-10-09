<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Read-only questions about a MySQL schema (information_schema), shared by the upgrade migrations and
 * `php artisan db:integrity`. Every query reads through the write connection so that DDL run a moment ago is visible.
 * Methods that take a $schema read that schema; without it they read the connection's own database.
 */
final class SchemaInspector
{
    /**
     * Every date / datetime / timestamp column of a real table (views are skipped).
     *
     * @return list<array{table: string, column: string, type: string, nullable: bool}>
     */
    public static function dateColumns(): array
    {
        $rows = DB::select(
            "SELECT c.TABLE_NAME AS tbl, c.COLUMN_NAME AS col, c.DATA_TYPE AS type, c.IS_NULLABLE AS nullable
             FROM information_schema.COLUMNS c
             JOIN information_schema.TABLES t
               ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME AND t.TABLE_TYPE = 'BASE TABLE'
             WHERE c.TABLE_SCHEMA = DATABASE() AND c.DATA_TYPE IN ('date', 'datetime', 'timestamp')
             ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",
            [],
            false
        );

        return array_map(fn ($row): array => [
            'table' => $row->tbl,
            'column' => $row->col,
            'type' => $row->type,
            'nullable' => $row->nullable === 'YES',
        ], $rows);
    }

    /**
     * The storage engine of every real table (views are not listed).
     *
     * @return array<string, string> table => engine
     */
    public static function tableEngines(): array
    {
        $rows = DB::select(
            "SELECT TABLE_NAME AS tbl, ENGINE AS engine FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME",
            [],
            false
        );

        return array_column(array_map(fn ($row): array => [(string) $row->tbl, (string) $row->engine], $rows), 1, 0);
    }

    /**
     * The column type exactly as MySQL reports it ("bigint unsigned", "int", "varchar(191)"), or null when the table or
     * column does not exist.
     */
    public static function columnType(string $table, string $column): ?string
    {
        return DB::selectOne(
            'SELECT COLUMN_TYPE AS type FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column],
            false
        )?->type;
    }

    /**
     * Every foreign key of the schema, in the same shape as ForeignKeyCatalog::all().
     *
     * @return list<array{table: string, name: string, columns: list<string>, refTable: string, refColumns: list<string>, onDelete: string, onUpdate: string}>
     */
    public static function foreignKeys(?string $schema = null): array
    {
        $rows = DB::select(
            'SELECT k.TABLE_NAME AS tbl, k.CONSTRAINT_NAME AS name, k.COLUMN_NAME AS col,
                    k.REFERENCED_TABLE_NAME AS ref_table, k.REFERENCED_COLUMN_NAME AS ref_col,
                    r.DELETE_RULE AS on_delete, r.UPDATE_RULE AS on_update
             FROM information_schema.KEY_COLUMN_USAGE k
             JOIN information_schema.REFERENTIAL_CONSTRAINTS r
               ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND r.TABLE_NAME = k.TABLE_NAME
             WHERE k.TABLE_SCHEMA = IFNULL(?, DATABASE()) AND k.REFERENCED_TABLE_NAME IS NOT NULL
             ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION',
            [$schema],
            false
        );

        $keys = [];
        foreach ($rows as $row) {
            $id = $row->tbl.'.'.$row->name;
            $keys[$id] ??= [
                'table' => $row->tbl,
                'name' => $row->name,
                'columns' => [],
                'refTable' => $row->ref_table,
                'refColumns' => [],
                'onDelete' => $row->on_delete,
                'onUpdate' => $row->on_update,
            ];
            $keys[$id]['columns'][] = $row->col;
            $keys[$id]['refColumns'][] = $row->ref_col;
        }

        return array_values($keys);
    }

    /**
     * The structure of a whole schema: per table its engine, collation, columns and indexes (an index is identified by
     * its kind and columns, not its name), the views, and the foreign keys (identified by their definition).
     *
     * @return array{
     *     tables: array<string, array{engine: string, collation: ?string, columns: array<string, array{type: string, nullable: bool, default: ?string, extra: string, collation: ?string}>, indexes: array<string, string>}>,
     *     views: list<string>,
     *     foreignKeys: array<string, string>
     * }
     */
    public static function describe(?string $schema = null): array
    {
        $tables = [];
        $views = [];
        foreach (DB::select(
            'SELECT TABLE_NAME AS tbl, TABLE_TYPE AS kind, ENGINE AS engine, TABLE_COLLATION AS collation
             FROM information_schema.TABLES WHERE TABLE_SCHEMA = IFNULL(?, DATABASE()) ORDER BY TABLE_NAME',
            [$schema],
            false
        ) as $row) {
            if ($row->kind === 'VIEW') {
                $views[] = $row->tbl;

                continue;
            }
            $tables[$row->tbl] = ['engine' => (string) $row->engine, 'collation' => $row->collation, 'columns' => [], 'indexes' => []];
        }

        foreach (DB::select(
            'SELECT TABLE_NAME AS tbl, COLUMN_NAME AS col, COLUMN_TYPE AS type, IS_NULLABLE AS nullable,
                    COLUMN_DEFAULT AS dflt, EXTRA AS extra, COLLATION_NAME AS collation
             FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = IFNULL(?, DATABASE()) ORDER BY TABLE_NAME, ORDINAL_POSITION',
            [$schema],
            false
        ) as $row) {
            if (isset($tables[$row->tbl])) {
                $tables[$row->tbl]['columns'][$row->col] = [
                    'type' => $row->type,
                    'nullable' => $row->nullable === 'YES',
                    'default' => $row->dflt,
                    'extra' => (string) $row->extra,
                    'collation' => $row->collation,
                ];
            }
        }

        $indexes = [];
        foreach (DB::select(
            'SELECT TABLE_NAME AS tbl, INDEX_NAME AS name, NON_UNIQUE AS non_unique, COLUMN_NAME AS col, SUB_PART AS sub_part
             FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = IFNULL(?, DATABASE()) ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX',
            [$schema],
            false
        ) as $row) {
            $indexes[$row->tbl][$row->name]['unique'] = ! $row->non_unique;
            $indexes[$row->tbl][$row->name]['columns'][] = $row->col.($row->sub_part ? "({$row->sub_part})" : '');
        }
        foreach ($indexes as $table => $byName) {
            foreach ($byName as $name => $index) {
                $kind = $name === 'PRIMARY' ? 'primary' : ($index['unique'] ? 'unique' : 'index');
                if (isset($tables[$table])) {
                    $tables[$table]['indexes'][$kind.'('.implode(',', $index['columns']).')'] = $name;
                }
            }
        }

        $foreignKeys = [];
        foreach (self::foreignKeys($schema) as $key) {
            $foreignKeys[self::describeKey($key)] = $key['table'].'.'.$key['name'];
        }

        return ['tables' => $tables, 'views' => $views, 'foreignKeys' => $foreignKeys];
    }

    /**
     * "visits(patient_id) -> patients(id) ON DELETE CASCADE ON UPDATE NO ACTION"
     *
     * @param  array{table: string, columns: list<string>, refTable: string, refColumns: list<string>, onDelete: string, onUpdate: string}  $key
     */
    public static function describeKey(array $key): string
    {
        return sprintf(
            '%s(%s) -> %s(%s) ON DELETE %s ON UPDATE %s',
            $key['table'],
            implode(',', $key['columns']),
            $key['refTable'],
            implode(',', $key['refColumns']),
            $key['onDelete'],
            $key['onUpdate']
        );
    }

    /**
     * Quote a table or column name for use in raw SQL.
     */
    public static function quote(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
