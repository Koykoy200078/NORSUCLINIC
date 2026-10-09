<?php

namespace Tests\Feature\Regression;

use Illuminate\Support\Facades\DB;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * Plan 0.2: the suite starts on a machine that has no test schema yet (no manual CREATE DATABASE), and it can never
 * be pointed at a live clinic database. Both rules live in tests/CreatesApplication.php.
 */
class TestDatabaseBootstrapTest extends TestCase
{
    private ?string $probeDatabase = null;

    protected function tearDown(): void
    {
        try {
            DB::purge('bootstrap_probe');

            if ($this->probeDatabase !== null) {
                DB::statement('DROP DATABASE IF EXISTS `'.$this->probeDatabase.'`');
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_a_missing_test_schema_is_created_with_the_connection_charset_and_collation(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('The test schema bootstrap is MySQL/MariaDB only.');
        }

        $this->probeDatabase = 'bootstrap_probe_'.bin2hex(random_bytes(6)).'_test';
        config(['database.connections.bootstrap_probe' => array_merge(
            DB::connection()->getConfig(),
            ['database' => $this->probeDatabase, 'url' => null]
        )]);
        $this->assertNull($this->probeSchema(), 'The probe schema must not exist before the bootstrap runs.');

        $this->bootstrap()->ensureTestSchemaExists($this->app, 'bootstrap_probe');

        $schema = $this->probeSchema();
        $this->assertNotNull($schema, 'The missing test schema was not created.');
        $this->assertSame('utf8mb4', $schema->charset);
        $this->assertSame('utf8mb4_unicode_ci', $schema->collation);
        $this->assertEquals(1, DB::connection('bootstrap_probe')->selectOne('select 1 as ok')->ok);

        // A second run finds the schema and leaves it alone.
        $this->bootstrap()->ensureTestSchemaExists($this->app, 'bootstrap_probe');
        $this->assertNotNull($this->probeSchema());
    }

    /**
     * @dataProvider databases
     */
    public function test_only_dedicated_test_databases_pass_the_destructive_test_guard(string $driver, string $database, bool $safe): void
    {
        $this->assertSame($safe, $this->bootstrap()->isSafeTestDatabase($driver, $database));
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function databases(): array
    {
        return [
            'live clinic schema' => ['mysql', 'norsu_clinic', false],
            'dedicated test schema' => ['mysql', 'norsu_clinic_test', true],
            'throwaway fixture schema' => ['mysql', 'legacy_defaults_0a1b2c3d4e5f_test', true],
            'mariadb test schema' => ['mariadb', 'norsu_clinic_test', true],
            'test suffix only in the middle' => ['mysql', 'norsu_clinic_test_backup', false],
            'quote character in the name' => ['mysql', 'norsu`_test', false],
            'empty name' => ['mysql', '', false],
            'in-memory sqlite' => ['sqlite', ':memory:', true],
            'sqlite file' => ['sqlite', 'database/database.sqlite', false],
            'unsupported driver' => ['pgsql', 'norsu_clinic_test', false],
        ];
    }

    /**
     * The two protected bootstrap methods, made callable.
     */
    private function bootstrap(): object
    {
        return new class
        {
            use CreatesApplication {
                ensureTestSchemaExists as public;
                isSafeTestDatabase as public;
            }
        };
    }

    private function probeSchema(): ?object
    {
        return DB::selectOne(
            'select default_character_set_name as charset, default_collation_name as collation
             from information_schema.schemata where schema_name = ?',
            [$this->probeDatabase]
        );
    }
}
