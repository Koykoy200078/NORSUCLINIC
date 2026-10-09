<?php

namespace Tests\Feature\Regression;

use App\Support\ForeignKeyCatalog;
use App\Support\SchemaInspector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.4 / 1.5: ForeignKeyCatalog is the list of foreign keys a correct database has. The upgrade migration
 * adds the missing ones from it and `db:integrity` reports the absent ones from it, so it must equal what the
 * migrations really create. This test fails the moment a migration adds, renames or changes a foreign key without
 * the catalog being updated.
 */
class ForeignKeyCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalog_lists_exactly_the_foreign_keys_a_fresh_install_has(): void
    {
        $this->assertSame(
            $this->describe(SchemaInspector::foreignKeys()),
            $this->describe(ForeignKeyCatalog::all()),
            'ForeignKeyCatalog is out of date: update app/Support/ForeignKeyCatalog.php to match the migrations.'
        );
    }

    public function test_the_catalog_has_47_keys_each_with_a_unique_name(): void
    {
        $names = array_column(ForeignKeyCatalog::all(), 'name');

        $this->assertCount(47, $names);
        $this->assertSame($names, array_values(array_unique($names)));
    }

    /**
     * @param  list<array<string, mixed>>  $keys
     * @return list<string>
     */
    private function describe(array $keys): array
    {
        $lines = array_map(
            fn (array $key): string => sprintf(
                '%s.%s (%s) -> %s (%s) ON DELETE %s ON UPDATE %s',
                $key['table'],
                $key['name'],
                implode(',', $key['columns']),
                $key['refTable'],
                implode(',', $key['refColumns']),
                $key['onDelete'],
                $key['onUpdate']
            ),
            $keys
        );
        sort($lines);

        return $lines;
    }
}
