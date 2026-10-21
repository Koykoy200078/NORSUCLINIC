<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Merged inventory identities are retained for the audit, while ordinary unused-record deletion is unchanged. */
trait ExcludesMergedInventory
{
    private static array $mergeSchemaCache = [];

    public static function bootExcludesMergedInventory(): void
    {
        static::addGlobalScope('not_merged', function (Builder $query): void {
            if (static::supportsInventoryMerges()) {
                $query->whereNull($query->getModel()->qualifyColumn('merged_into_id'));
            }
        });
    }

    public static function supportsInventoryMerges(): bool
    {
        $table = (new static)->getTable();
        $key = DB::connection()->getDatabaseName().':'.$table;

        return static::$mergeSchemaCache[$key] ??= Schema::hasColumn($table, 'merged_into_id');
    }

    /** Migrations may introduce the columns after an older data migration has already queried a model. */
    public static function forgetMergeSchemaCache(): void
    {
        static::$mergeSchemaCache = [];
    }

    public function scopeWithMerged(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_merged');
    }

    /** A stale label remains an alias of its retained identity rather than creating the duplicate again. */
    public static function resolveInventoryLabel(string $name, array $defaults = []): static
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Resolve inventory labels inside the save transaction.');
        }
        $record = static::withMerged()->lockForUpdate()->firstOrCreate(['name' => $name], $defaults);
        $seen = [];
        while ($record->merged_into_id !== null) {
            if (isset($seen[$record->id])) {
                throw new \RuntimeException('The merged label chain is invalid. Choose the retained label again.');
            }
            $seen[$record->id] = true;
            $record = static::withMerged()->lockForUpdate()->findOrFail($record->merged_into_id);
        }

        return $record;
    }

    public function mergedSources(): HasMany
    {
        return $this->hasMany(static::class, 'merged_into_id')->withoutGlobalScope('not_merged');
    }
}
