<?php

namespace App\Support;

/**
 * Names every difference between two schema descriptions from SchemaInspector::describe(): "expected" is a fresh
 * install, "actual" is the database being checked. Column order, index names and constraint names are ignored (an
 * index is the same index under another name); everything that changes behaviour is not.
 */
final class SchemaParity
{
    /**
     * @param  array<string, mixed>  $expected  describe() of a fresh install
     * @param  array<string, mixed>  $actual  describe() of the database being checked
     * @return list<string> one line per difference, empty when the schemas are the same
     */
    public static function differences(array $expected, array $actual): array
    {
        $lines = [];

        foreach (array_diff_key($expected['tables'], $actual['tables']) as $table => $_) {
            $lines[] = "table {$table} is missing";
        }
        foreach (array_diff_key($actual['tables'], $expected['tables']) as $table => $_) {
            $lines[] = "table {$table} is not in a fresh install";
        }
        foreach (array_diff($expected['views'], $actual['views']) as $view) {
            $lines[] = "view {$view} is missing";
        }
        foreach (array_diff($actual['views'], $expected['views']) as $view) {
            $lines[] = "view {$view} is not in a fresh install";
        }

        foreach (array_intersect_key($expected['tables'], $actual['tables']) as $table => $fresh) {
            $found = $actual['tables'][$table];

            if ($fresh['engine'] !== $found['engine']) {
                $lines[] = "{$table}: engine {$fresh['engine']} expected, {$found['engine']} found";
            }
            if ($fresh['collation'] !== $found['collation']) {
                $lines[] = "{$table}: collation {$fresh['collation']} expected, {$found['collation']} found";
            }

            foreach (array_diff_key($fresh['columns'], $found['columns']) as $column => $_) {
                $lines[] = "{$table}.{$column}: column is missing";
            }
            foreach (array_diff_key($found['columns'], $fresh['columns']) as $column => $_) {
                $lines[] = "{$table}.{$column}: column is not in a fresh install";
            }
            foreach (array_intersect_key($fresh['columns'], $found['columns']) as $column => $want) {
                $has = $found['columns'][$column];
                if ($want['type'] !== $has['type']) {
                    $lines[] = "{$table}.{$column}: type {$want['type']} expected, {$has['type']} found";
                }
                if ($want['nullable'] !== $has['nullable']) {
                    $lines[] = "{$table}.{$column}: ".($want['nullable'] ? 'NULL' : 'NOT NULL').' expected, '.($has['nullable'] ? 'NULL' : 'NOT NULL').' found';
                }
                if ($want['default'] !== $has['default']) {
                    $lines[] = "{$table}.{$column}: default ".self::show($want['default']).' expected, '.self::show($has['default']).' found';
                }
                if ($want['extra'] !== $has['extra']) {
                    $lines[] = "{$table}.{$column}: extra '{$want['extra']}' expected, '{$has['extra']}' found";
                }
                if ($want['collation'] !== $has['collation']) {
                    $lines[] = "{$table}.{$column}: collation ".self::show($want['collation']).' expected, '.self::show($has['collation']).' found';
                }
            }

            foreach (array_diff_key($fresh['indexes'], $found['indexes']) as $index => $name) {
                $lines[] = "{$table}: ".self::describeIndex($index).' is missing';
            }
            foreach (array_diff_key($found['indexes'], $fresh['indexes']) as $index => $name) {
                $lines[] = "{$table}: ".self::describeIndex($index).' is not in a fresh install';
            }
        }

        foreach (array_diff_key($expected['foreignKeys'], $actual['foreignKeys']) as $key => $name) {
            $lines[] = "foreign key {$key} is missing";
        }
        foreach (array_diff_key($actual['foreignKeys'], $expected['foreignKeys']) as $key => $name) {
            $lines[] = "foreign key {$key} is not in a fresh install";
        }

        return $lines;
    }

    /**
     * "unique(name)" => "unique index (name)"
     */
    private static function describeIndex(string $signature): string
    {
        preg_match('/^(\w+)\((.*)\)$/', $signature, $match);

        return match ($match[1] ?? '') {
            'primary' => "primary key ({$match[2]})",
            'unique' => "unique index ({$match[2]})",
            default => "index ({$match[2]})",
        };
    }

    private static function show(?string $value): string
    {
        return $value === null ? 'NULL' : "'{$value}'";
    }
}
