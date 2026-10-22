<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Whether a column exists, for code that must also run on a database that has not had its migration yet.
 * Schema::hasColumn is a query against information_schema every time; the answer cannot change while a request runs, so
 * it is asked once per application instance (one HTTP request, one artisan run, one test), like
 * Notification::supportsClinicAlerts().
 */
final class SchemaColumns
{
    public static function has(string $table, string $column): bool
    {
        $key = 'schema.column.'.DB::connection()->getDatabaseName().'.'.$table.'.'.$column;
        if (! app()->bound($key)) {
            app()->instance($key, Schema::hasColumn($table, $column));
        }

        return app($key);
    }
}
