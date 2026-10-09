<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Upgrade steps for a clinic database that was created on an old server: MyISAM tables, no foreign keys, zero dates.
 * The Phase 1 migrations call these; every step is written so that a second run finds nothing left to do.
 */
final class LegacySchemaUpgrade
{
    /**
     * Convert every MyISAM (or Aria) table to InnoDB. Foreign keys, transactions and row locks exist only on InnoDB,
     * and the clinic's old server created everything as MyISAM, so none of them ever worked there. Rows, indexes and
     * the next auto-increment value are kept; views are not touched.
     *
     * @return list<string> the tables converted (empty on a database that is already InnoDB)
     */
    public static function convertToInnoDb(): array
    {
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            return [];
        }

        // Read through the write connection: the DDL of an earlier migration must be visible.
        $tables = array_column($connection->select(
            "SELECT TABLE_NAME AS name FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE IN ('MyISAM', 'Aria')
             ORDER BY TABLE_NAME",
            [],
            false
        ), 'name');

        $converted = [];
        // Copying a table into InnoDB re-validates old zero dates, which strict mode would refuse.
        LegacyMysqlMigration::withoutZeroDateChecks(function () use ($connection, $tables, &$converted) {
            foreach ($tables as $table) {
                $connection->statement('ALTER TABLE '.SchemaInspector::quote($table).' ENGINE = InnoDB');
                $converted[] = $table;
            }
        });

        return $converted;
    }

    /**
     * Turn every zero or half-zero date ('0000-00-00', '2027-00-15') into NULL where the column allows NULL. The old
     * server stored those for "no date"; strict MySQL refuses to rewrite a row that holds one. A NOT NULL column is
     * only reported, never changed.
     *
     * @return array{fixed: array<string, int>, blocked: array<string, int>} "table.column" => number of rows
     */
    public static function normalizeZeroDates(): array
    {
        $report = ['fixed' => [], 'blocked' => []];
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $report;
        }

        foreach (SchemaInspector::dateColumns() as $column) {
            $table = SchemaInspector::quote($column['table']);
            $name = SchemaInspector::quote($column['column']);
            $isZero = "(YEAR({$name}) = 0 OR MONTH({$name}) = 0 OR DAY({$name}) = 0)";

            $rows = (int) ($connection->selectOne("SELECT COUNT(*) AS n FROM {$table} WHERE {$isZero}", [], false)->n ?? 0);
            if ($rows === 0) {
                continue;
            }

            $where = $column['table'].'.'.$column['column'];
            if (! $column['nullable']) {
                $report['blocked'][$where] = $rows;

                continue;
            }

            // Not needed on MySQL 9, but older servers re-validate the other zero dates of the row they rewrite.
            LegacyMysqlMigration::withoutZeroDateChecks(
                fn () => $connection->update("UPDATE {$table} SET {$name} = NULL WHERE {$isZero}")
            );
            $report['fixed'][$where] = $rows;
        }

        return $report;
    }

    /**
     * Add every foreign key of ForeignKeyCatalog that the database lacks, without changing a single row. A key is added
     * only when both tables exist and are InnoDB, the two columns have the same type, and no row points at a parent that
     * does not exist; otherwise it is skipped and the reason is returned so it can be fixed by hand.
     *
     * @return array{added: list<string>, present: list<string>, skipped: array<string, string>} by constraint name
     */
    public static function restoreForeignKeys(): array
    {
        $report = ['added' => [], 'present' => [], 'skipped' => []];
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true) || $connection->pretending()) {
            return $report;
        }

        $engines = SchemaInspector::tableEngines();
        $existing = SchemaInspector::foreignKeys();

        foreach (ForeignKeyCatalog::all() as $key) {
            if (self::hasEquivalentKey($existing, $key)) {
                $report['present'][] = $key['name'];

                continue;
            }

            if (($reason = self::whyKeyCannotBeAdded($key, $engines)) !== null) {
                $report['skipped'][$key['name']] = $reason;

                continue;
            }

            try {
                // Adding a key copies the table, which re-validates old zero dates in its other columns.
                LegacyMysqlMigration::withoutZeroDateChecks(fn () => $connection->statement(self::addKeySql($key)));
                $report['added'][] = $key['name'];
            } catch (QueryException $e) {
                $report['skipped'][$key['name']] = 'MySQL refused it: '.mb_substr(strtok($e->getMessage(), "\n"), 0, 200);
            }
        }

        return $report;
    }

    /**
     * Tell whoever runs `php artisan migrate` what an upgrade step did. A problem is also written to the log.
     */
    public static function announce(string $message, bool $problem = false): void
    {
        if ($problem) {
            Log::warning('[clinic upgrade] '.$message);
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            fwrite(STDOUT, '   '.$message.PHP_EOL);
        }
    }

    /**
     * Whether a foreign key on the same columns to the same parent exists already, whatever it is called.
     *
     * @param  list<array<string, mixed>>  $existing
     * @param  array<string, mixed>  $key
     */
    private static function hasEquivalentKey(array $existing, array $key): bool
    {
        foreach ($existing as $other) {
            if ($other['table'] === $key['table'] && $other['columns'] === $key['columns']
                && $other['refTable'] === $key['refTable'] && $other['refColumns'] === $key['refColumns']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $key  an entry of ForeignKeyCatalog::all()
     * @param  array<string, string>  $engines  SchemaInspector::tableEngines()
     * @return string|null why the key cannot be added without touching data, or null when it can
     */
    public static function whyKeyCannotBeAdded(array $key, array $engines): ?string
    {
        [$table, $parent] = [$key['table'], $key['refTable']];

        foreach ([$table, $parent] as $name) {
            if (! isset($engines[$name])) {
                return "table {$name} does not exist";
            }
            if ($engines[$name] !== 'InnoDB') {
                return "table {$name} is {$engines[$name]}, not InnoDB";
            }
        }

        if (count($key['columns']) !== 1) {
            return 'a key over several columns is not supported here';
        }
        [$column, $referenced] = [$key['columns'][0], $key['refColumns'][0]];

        $childType = SchemaInspector::columnType($table, $column);
        $parentType = SchemaInspector::columnType($parent, $referenced);
        if ($childType === null || $parentType === null) {
            return 'column '.($childType === null ? "{$table}.{$column}" : "{$parent}.{$referenced}").' does not exist';
        }
        if (self::normalizedType($childType) !== self::normalizedType($parentType)) {
            return "column type differs ({$table}.{$column} is {$childType}, {$parent}.{$referenced} is {$parentType})";
        }

        $from = 'FROM '.SchemaInspector::quote($table).' c LEFT JOIN '.SchemaInspector::quote($parent).' p ON p.'
            .SchemaInspector::quote($referenced).' = c.'.SchemaInspector::quote($column)
            .' WHERE c.'.SchemaInspector::quote($column).' IS NOT NULL AND p.'.SchemaInspector::quote($referenced).' IS NULL';
        $orphans = (int) (DB::selectOne("SELECT COUNT(*) AS n {$from}", [], false)->n ?? 0);
        if ($orphans > 0) {
            $values = DB::select(
                'SELECT c.'.SchemaInspector::quote($column)." AS value, COUNT(*) AS n {$from} GROUP BY c.".SchemaInspector::quote($column)
                .' ORDER BY c.'.SchemaInspector::quote($column).' LIMIT 10',
                [],
                false
            );
            $listed = implode(', ', array_map(fn ($row): string => "{$row->value} (x{$row->n})", $values));

            return "{$orphans} row(s) of {$table}.{$column} point at {$parent} rows that do not exist (values: {$listed}"
                .($orphans > array_sum(array_column($values, 'n')) ? ', ...' : '').')';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $key
     */
    private static function addKeySql(array $key): string
    {
        $quote = fn (string $name): string => SchemaInspector::quote($name);
        $sql = sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s',
            $quote($key['table']),
            $quote($key['name']),
            implode(', ', array_map($quote, $key['columns'])),
            $quote($key['refTable']),
            implode(', ', array_map($quote, $key['refColumns'])),
            $key['onDelete']
        );

        return $key['onUpdate'] === 'NO ACTION' ? $sql : $sql.' ON UPDATE '.$key['onUpdate'];
    }

    /**
     * "bigint(20) unsigned" (older servers) and "bigint unsigned" are the same type.
     */
    private static function normalizedType(string $type): string
    {
        return (string) preg_replace('/^(tinyint|smallint|mediumint|int|bigint)\(\d+\)/', '$1', strtolower($type));
    }
}
