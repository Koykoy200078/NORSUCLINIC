<?php

namespace Tests\Feature\Upgrade;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A fresh install (every migration run on an empty schema, which is what the test schema is) must keep building the same
 * columns. Laravel 11 changed how float() / double() and ->change() behave: a ->change() now DROPS every modifier that is
 * not repeated, so a legacy migration could quietly build another column type, default or nullability than the clinic's
 * database has. This test pins the shape of every column (type, nullability, default, extra, collation, comment) against
 * tests/Fixtures/schema/fresh-install-columns.json, captured on Laravel 10.
 *
 * A deliberate change of the schema (a new migration) updates the fixture:
 *   UPDATE_SCHEMA_FIXTURE=1 vendor/bin/phpunit --filter FreshInstallSchemaShapeTest
 * (docs/laravel-13-upgrade-plan.md, finding C14; the real-dump check is `php artisan db:integrity --compare-fresh`).
 */
class FreshInstallSchemaShapeTest extends TestCase
{
    use RefreshDatabase;

    private const FIXTURE = __DIR__ . '/../../Fixtures/schema/fresh-install-columns.json';

    /**
     * @return array<string, array<string, mixed>>
     */
    private function columns(): array
    {
        $rows = DB::select('SELECT TABLE_NAME AS t, COLUMN_NAME AS c, COLUMN_TYPE AS type, IS_NULLABLE AS nullable, COLUMN_DEFAULT AS def,
                EXTRA AS extra, COLLATION_NAME AS collation, COLUMN_COMMENT AS comment
            FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME, ORDINAL_POSITION');

        $shape = [];
        foreach ($rows as $row) {
            $shape[$row->t . '.' . $row->c] = [
                'type' => $row->type, 'nullable' => $row->nullable, 'default' => $row->def,
                'extra' => $row->extra, 'collation' => $row->collation, 'comment' => $row->comment,
            ];
        }

        return $shape;
    }

    public function test_every_column_of_a_fresh_install_keeps_its_shape(): void
    {
        $actual = $this->columns();

        if (getenv('UPDATE_SCHEMA_FIXTURE') === '1') {
            file_put_contents(self::FIXTURE, json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
            $this->markTestSkipped('Fixture rewritten: ' . count($actual) . ' columns.');
        }

        $expected = json_decode(file_get_contents(self::FIXTURE), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([], array_values(array_diff(array_keys($expected), array_keys($actual))), 'Columns that a fresh install no longer builds');
        $this->assertSame([], array_values(array_diff(array_keys($actual), array_keys($expected))), 'Columns that a fresh install now builds and the fixture does not know');

        $changed = [];
        foreach ($expected as $column => $shape) {
            if ($actual[$column] !== $shape) {
                $changed[$column] = ['was' => $shape, 'now' => $actual[$column]];
            }
        }
        $this->assertSame([], $changed, 'Columns whose type, nullability, default, extra, collation or comment changed');
    }
}
