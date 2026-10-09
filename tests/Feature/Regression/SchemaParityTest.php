<?php

namespace Tests\Feature\Regression;

use App\Support\SchemaInspector;
use App\Support\SchemaParity;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.5 (`db:integrity --compare-fresh`): after the upgrade the clinic's schema must be the same as the
 * schema of a fresh install. SchemaInspector::describe() reads one schema; SchemaParity names every difference.
 */
class SchemaParityTest extends TestCase
{
    use UsesFixtureDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('schema_parity');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_describe_reads_tables_columns_indexes_views_and_foreign_keys(): void
    {
        $this->freshShape('');

        $schema = SchemaInspector::describe();

        $this->assertSame(['patients', 'visits'], array_keys($schema['tables']));
        $this->assertSame('InnoDB', $schema['tables']['patients']['engine']);
        $this->assertSame(
            ['type' => 'varchar(100)', 'nullable' => false, 'default' => null, 'extra' => '', 'collation' => 'utf8mb4_unicode_ci'],
            $schema['tables']['patients']['columns']['name']
        );
        $this->assertSame('auto_increment', $schema['tables']['patients']['columns']['id']['extra']);
        $this->assertContains('unique(name)', array_keys($schema['tables']['patients']['indexes']));
        $this->assertContains('index(patient_id)', array_keys($schema['tables']['visits']['indexes']));
        $this->assertSame(['patient_names'], $schema['views']);
        $this->assertCount(1, $schema['foreignKeys']);
    }

    public function test_the_same_schema_has_no_differences(): void
    {
        $other = $this->createExtraFixtureDatabase();
        $this->freshShape('');
        $this->freshShape("`{$other}`.");

        $this->assertSame([], SchemaParity::differences(SchemaInspector::describe($other), SchemaInspector::describe()));
    }

    public function test_every_kind_of_drift_is_named(): void
    {
        $fresh = $this->createExtraFixtureDatabase();
        $this->freshShape("`{$fresh}`.");

        // The clinic's old shape: MyISAM, a narrower nullable name, no unique index, an extra column, no foreign key,
        // an extra table and no view.
        DB::statement('CREATE TABLE `patients` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(50) NULL, `legacy_note` TEXT NULL
        ) ENGINE=MyISAM');
        DB::statement('CREATE TABLE `visits` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `patient_id` BIGINT UNSIGNED NOT NULL,
            KEY `visits_patient_id_foreign` (`patient_id`)
        ) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `old_stuff` (`id` INT PRIMARY KEY) ENGINE=MyISAM');

        $differences = implode("\n", SchemaParity::differences(SchemaInspector::describe($fresh), SchemaInspector::describe()));

        $this->assertStringContainsString('patients: engine InnoDB expected, MyISAM found', $differences);
        $this->assertStringContainsString('patients.name: type varchar(100) expected, varchar(50) found', $differences);
        $this->assertStringContainsString('patients.name: NOT NULL expected, NULL found', $differences);
        $this->assertStringContainsString('patients.legacy_note: column is not in a fresh install', $differences);
        $this->assertStringContainsString('patients: unique index (name) is missing', $differences);
        $this->assertStringContainsString('foreign key visits(patient_id) -> patients(id) ON DELETE CASCADE ON UPDATE NO ACTION is missing', $differences);
        $this->assertStringContainsString('table old_stuff is not in a fresh install', $differences);
        $this->assertStringContainsString('view patient_names is missing', $differences);
    }

    public function test_an_index_under_another_name_is_the_same_index(): void
    {
        $fresh = $this->createExtraFixtureDatabase();
        DB::statement("CREATE TABLE `{$fresh}`.`t` (`id` INT PRIMARY KEY, `code` VARCHAR(20), UNIQUE KEY `t_code_unique` (`code`)) ENGINE=InnoDB");
        DB::statement('CREATE TABLE `t` (`id` INT PRIMARY KEY, `code` VARCHAR(20), UNIQUE KEY `my_own_name` (`code`)) ENGINE=InnoDB');

        $this->assertSame([], SchemaParity::differences(SchemaInspector::describe($fresh), SchemaInspector::describe()));
    }

    /**
     * patients + visits (foreign key) + a view, in the current schema or in the schema named by the prefix.
     */
    private function freshShape(string $prefix): void
    {
        DB::statement("CREATE TABLE {$prefix}`patients` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL,
            UNIQUE KEY `patients_name_unique` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        DB::statement("CREATE TABLE {$prefix}`visits` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `patient_id` BIGINT UNSIGNED NOT NULL,
            KEY `visits_patient_id_foreign` (`patient_id`),
            CONSTRAINT `visits_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES {$prefix}`patients` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        DB::statement("CREATE VIEW {$prefix}`patient_names` AS SELECT `id`, `name` FROM {$prefix}`patients`");
    }
}
