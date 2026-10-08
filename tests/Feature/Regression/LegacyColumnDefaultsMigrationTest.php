<?php

namespace Tests\Feature\Regression;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyColumnDefaultsMigrationTest extends TestCase
{
    private ?Connection $adminConnection = null;
    private ?string $fixtureDatabase = null;
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();
        $this->adminConnection = DB::connection();
        if ($this->adminConnection->getDriverName() !== 'mysql') {
            $this->markTestSkipped('This regression requires MySQL/MariaDB.');
        }

        // Never alter the application's tables or the shared test-suite schema.
        $database = 'legacy_defaults_'.bin2hex(random_bytes(6)).'_test';
        $this->adminConnection->statement('CREATE DATABASE `'.$database.'`');
        $this->fixtureDatabase = $database;
        config(['database.connections.legacy_defaults_fixture' => array_merge(
            $this->adminConnection->getConfig(),
            ['database' => $database, 'url' => null, 'prefix' => '']
        )]);
        DB::setDefaultConnection('legacy_defaults_fixture');
    }

    protected function tearDown(): void
    {
        try {
            if ($this->fixtureDatabase !== null) {
                DB::purge('legacy_defaults_fixture');
                DB::setDefaultConnection($this->originalConnection);
                $this->adminConnection->statement('DROP DATABASE `'.$this->fixtureDatabase.'`');
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_existing_zero_dates_survive_the_upgrade_and_a_retry_in_strict_mode(): void
    {
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
        DB::statement('CREATE TABLE `medicines` (
            `id` INT PRIMARY KEY, `salt_composition` VARCHAR(191) NOT NULL,
            `quantity` INT NOT NULL, `available_quantity` INT NOT NULL,
            `created_at` DATETIME NOT NULL
        )');
        DB::statement("INSERT INTO `medicines` VALUES (1, 'Original composition', 9, 7, '0000-00-00 00:00:00')");
        DB::statement('CREATE TABLE `sale_medicines` (
            `id` INT PRIMARY KEY, `expiry_date` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL, `sale_quantity` INT NOT NULL
        ) ENGINE=MyISAM'); // Force row copying, as older servers also do for this ALTER.
        DB::statement("INSERT INTO `sale_medicines` VALUES
            (1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 2),
            (2, '2027-00-15 00:00:00', '2026-10-01 12:00:00', 3),
            (3, '2028-02-29 13:14:15', '2026-10-01 12:00:00', 4)");
        // Simulate an earlier attempt that already committed the first ALTER.
        DB::statement('ALTER TABLE `medicines` MODIFY `salt_composition` VARCHAR(191) NULL');
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
        $originalMode = $this->sqlMode();
        $medicineBefore = DB::table('medicines')->first();
        $salesBefore = DB::table('sale_medicines')->orderBy('id')->get()->all();

        $migration = $this->migration();
        $migration->up();
        $migration->up();

        $this->assertSame($originalMode, $this->sqlMode());
        $this->assertEquals($medicineBefore, DB::table('medicines')->first());
        $this->assertEquals($salesBefore, DB::table('sale_medicines')->orderBy('id')->get()->all());
        $this->assertSame('YES', $this->column('sale_medicines', 'expiry_date')->is_nullable);
        $this->assertSame('0', (string) $this->column('medicines', 'quantity')->column_default);
        $this->assertSame('0', (string) $this->column('medicines', 'available_quantity')->column_default);

        DB::table('sale_medicines')->insert([
            'id' => 4, 'created_at' => '2026-10-08 12:00:00', 'sale_quantity' => 1,
        ]);
        $this->assertNull(DB::table('sale_medicines')->where('id', 4)->value('expiry_date'));
    }

    public function test_a_failed_alter_restores_sql_mode_and_keeps_other_strict_checks(): void
    {
        DB::statement('CREATE TABLE `medicines` (
            `id` INT PRIMARY KEY, `salt_composition` VARCHAR(191) NOT NULL,
            `quantity` VARCHAR(191) NOT NULL
        )');
        DB::statement("INSERT INTO `medicines` VALUES (1, 'Original composition', 'cannot-convert-to-int')");
        $originalMode = $this->sqlMode();

        try {
            $this->migration()->up();
            $this->fail('An invalid quantity must still fail instead of being silently coerced to zero.');
        } catch (QueryException $error) {
            $this->assertStringContainsString('quantity', $error->getSql());
        }

        $this->assertSame($originalMode, $this->sqlMode());
        $this->assertSame('cannot-convert-to-int', DB::table('medicines')->value('quantity'));
    }

    public function test_missing_legacy_tables_and_columns_are_skipped(): void
    {
        DB::statement('CREATE TABLE `medicines` (`id` INT PRIMARY KEY, `quantity` INT NOT NULL)');
        $originalMode = $this->sqlMode();

        $this->migration()->up();

        DB::table('medicines')->insert(['id' => 1]);
        $this->assertSame(0, (int) DB::table('medicines')->value('quantity'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_a_migration_preview_leaves_schema_and_sql_mode_unchanged(): void
    {
        DB::statement('CREATE TABLE `medicines` (`id` INT PRIMARY KEY, `quantity` INT NOT NULL)');
        $originalMode = $this->sqlMode();

        DB::connection()->pretend(fn () => $this->migration()->up());

        $this->assertNull($this->column('medicines', 'quantity')->column_default);
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_the_history_number_indexes_can_be_changed_with_legacy_zero_timestamps(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `medicine_bills` (
            `id` BIGINT UNSIGNED PRIMARY KEY, `history_number` VARCHAR(191) NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_medicine_bills_history_number` (`history_number`)
        ) ENGINE=MyISAM');
        DB::statement("INSERT INTO `medicine_bills` VALUES
            (1, 'LEGACY', '0000-00-00 00:00:00'), (2, 'LEGACY', '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_09_30_122000_make_dispense_history_number_unique.php');
        $migration->up();

        $this->assertSame(['LEGACY', 'LEGACY-2'], DB::table('medicine_bills')->orderBy('id')->pluck('history_number')->all());
        $this->assertSame(['0000-00-00 00:00:00', '0000-00-00 00:00:00'], DB::table('medicine_bills')->pluck('created_at')->all());
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_purchased_medicine_batch_foreign_keys_preserve_legacy_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `medicine_batches` (`id` BIGINT UNSIGNED PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `purchased_medicines` (
            `id` BIGINT UNSIGNED PRIMARY KEY, `medicine_id` BIGINT UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL
        ) ENGINE=InnoDB');
        DB::statement("INSERT INTO `purchased_medicines` VALUES (1, 1, '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_09_30_140000_link_purchased_medicines_to_batches.php');
        $migration->up();

        $this->assertSame('SET NULL', $this->foreignKeyDeleteRule('purchased_medicines_batch_id_foreign'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('purchased_medicines')->value('created_at'));
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertFalse(DB::connection()->getSchemaBuilder()->hasColumn('purchased_medicines', 'batch_id'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_restricting_medicine_deletion_preserves_legacy_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `medicines` (`id` BIGINT UNSIGNED PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('INSERT INTO `medicines` VALUES (1)');
        DB::statement('CREATE TABLE `prescriptions_medicines` (
            `id` BIGINT UNSIGNED PRIMARY KEY, `medicine` BIGINT UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL,
            CONSTRAINT `legacy_prescriptions_medicine_fk` FOREIGN KEY (`medicine`)
                REFERENCES `medicines` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB');
        DB::statement("INSERT INTO `prescriptions_medicines` VALUES (1, 1, '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_09_30_141000_restrict_delete_of_medicines_with_history.php');
        $migration->up();

        $this->assertSame('RESTRICT', $this->foreignKeyDeleteRule('legacy_prescriptions_medicine_fk'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('prescriptions_medicines')->value('created_at'));
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertSame('CASCADE', $this->foreignKeyDeleteRule('legacy_prescriptions_medicine_fk'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_sale_medicine_indexes_preserve_legacy_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `sale_medicines` (
            `id` BIGINT UNSIGNED PRIMARY KEY, `medicine_bill_id` BIGINT UNSIGNED NOT NULL,
            `medicine_id` BIGINT UNSIGNED NOT NULL, `expiry_date` DATETIME NULL
        ) ENGINE=MyISAM');
        DB::statement("INSERT INTO `sale_medicines` VALUES (1, 1, 1, '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_09_30_150000_add_sale_medicines_indexes.php');
        $migration->up();
        $migration->up();

        $indexes = DB::select('SELECT DISTINCT INDEX_NAME AS name FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', ['sale_medicines']);
        $this->assertEqualsCanonicalizing(['PRIMARY', 'idx_sale_medicines_bill', 'idx_sale_medicines_medicine'], array_column($indexes, 'name'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('sale_medicines')->value('expiry_date'));
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_widening_clinical_text_preserves_zero_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `document_issuances` (
            `id` INT PRIMARY KEY, `allergies` VARCHAR(191) NULL, `created_at` DATETIME NOT NULL
        ) ENGINE=MyISAM');
        DB::statement("INSERT INTO `document_issuances` VALUES (1, 'Original allergy', '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $this->loadMigration('2026_09_30_120000_widen_clinical_text_columns.php')->up();

        $this->assertSame('text', $this->column('document_issuances', 'allergies')->data_type);
        $this->assertSame('Original allergy', DB::table('document_issuances')->value('allergies'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('document_issuances')->value('created_at'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_prescription_parent_foreign_keys_preserve_zero_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        foreach (['doctors', 'patients'] as $table) {
            DB::statement('CREATE TABLE `'.$table.'` (`id` BIGINT UNSIGNED PRIMARY KEY) ENGINE=InnoDB');
            DB::table($table)->insert(['id' => 1]);
        }
        DB::statement('CREATE TABLE `prescriptions` (
            `id` BIGINT UNSIGNED PRIMARY KEY, `doctor_id` BIGINT UNSIGNED NOT NULL,
            `patient_id` BIGINT UNSIGNED NOT NULL, `created_at` DATETIME NOT NULL,
            CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
            CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB');
        DB::statement("INSERT INTO `prescriptions` VALUES (1, 1, 1, '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_09_30_130000_restrict_delete_of_doctors_and_patients_with_prescriptions.php');
        $migration->up();

        foreach (['prescriptions_doctor_id_foreign', 'prescriptions_patient_id_foreign'] as $constraint) {
            $this->assertSame('RESTRICT', $this->foreignKeyDeleteRule($constraint));
        }
        $this->assertSame('0000-00-00 00:00:00', DB::table('prescriptions')->value('created_at'));
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertSame('CASCADE', $this->foreignKeyDeleteRule('prescriptions_doctor_id_foreign'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_role_display_name_defaults_preserve_zero_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        foreach (['roles', 'permissions'] as $table) {
            DB::statement('CREATE TABLE `'.$table.'` (
                `id` INT PRIMARY KEY, `display_name` VARCHAR(255) NOT NULL, `created_at` DATETIME NOT NULL
            ) ENGINE=MyISAM');
            DB::table($table)->insert(['id' => 1, 'display_name' => 'Legacy', 'created_at' => '0000-00-00 00:00:00']);
        }
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $this->loadMigration('2026_09_30_160000_default_display_names_for_roles_and_permissions.php')->up();

        foreach (['roles', 'permissions'] as $table) {
            DB::table($table)->insert(['id' => 2, 'created_at' => '2026-10-08 12:00:00']);
            $this->assertSame('', DB::table($table)->where('id', 2)->value('display_name'));
            $this->assertSame('0000-00-00 00:00:00', DB::table($table)->where('id', 1)->value('created_at'));
        }
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_the_settings_unique_index_preserves_zero_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `settings` (
            `id` INT PRIMARY KEY, `key` VARCHAR(191) NOT NULL, `value` VARCHAR(191), `created_at` DATETIME NOT NULL
        ) ENGINE=MyISAM');
        DB::statement("INSERT INTO `settings` VALUES
            (1, 'clinic_name', 'Original', '0000-00-00 00:00:00'),
            (2, 'clinic_name', 'Duplicate', '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_09_30_170000_make_settings_key_unique.php');
        $migration->up();

        $this->assertSame(1, DB::table('settings')->count());
        $this->assertSame('Original', DB::table('settings')->value('value'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('settings')->value('created_at'));
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_consultation_snapshot_schema_and_backfill_preserve_zero_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        foreach (['campuses' => 'campus_name', 'colleges' => 'college_name', 'courses' => 'course_name', 'year_levels' => 'year_level_name'] as $table => $name) {
            DB::statement('CREATE TABLE `'.$table.'` (`id` INT PRIMARY KEY, `'.$name.'` VARCHAR(191))');
        }
        DB::table('campuses')->insert(['id' => 11, 'campus_name' => 'North']);
        DB::statement('CREATE TABLE `patient_types` (`id` INT PRIMARY KEY, `code` VARCHAR(191))');
        DB::table('patient_types')->insert(['id' => 21, 'code' => 'student']);
        DB::statement('CREATE TABLE `users` (`id` INT PRIMARY KEY)');
        DB::statement('CREATE TABLE `patients` (`id` INT PRIMARY KEY, `user_id` INT, `patient_type_id` INT)');
        DB::statement('CREATE TABLE `document_issuances` (
            `id` INT PRIMARY KEY, `user_id` INT NULL, `campus` VARCHAR(191), `college` VARCHAR(191),
            `course` VARCHAR(191), `year_level` VARCHAR(191), `informant` VARCHAR(191), `requested_at` DATETIME NOT NULL
        ) ENGINE=MyISAM');
        DB::table('document_issuances')->insert([
            'id' => 1, 'campus' => 'North', 'informant' => 'Student', 'requested_at' => '0000-00-00 00:00:00',
        ]);
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_10_02_090100_add_report_snapshot_columns_to_document_issuances.php');
        $migration->up();

        $document = DB::table('document_issuances')->first();
        $this->assertSame(11, (int) $document->campus_id);
        $this->assertSame(21, (int) $document->patient_type_id);
        $this->assertSame('0000-00-00 00:00:00', $document->requested_at);
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertFalse(DB::connection()->getSchemaBuilder()->hasColumn('document_issuances', 'campus_id'));
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_consultation_soft_deletes_preserve_zero_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `document_issuances` (`id` INT PRIMARY KEY, `requested_at` DATETIME NOT NULL) ENGINE=MyISAM');
        DB::statement("INSERT INTO `document_issuances` VALUES (1, '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_10_02_130000_add_soft_deletes_to_document_issuances.php');
        $migration->up();

        $this->assertNull(DB::table('document_issuances')->value('deleted_at'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('document_issuances')->value('requested_at'));
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertSame($originalMode, $this->sqlMode());
    }

    public function test_lab_soft_deletes_and_stock_in_indexes_preserve_zero_dates(): void
    {
        $originalMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `lab_requests` (`id` INT PRIMARY KEY, `created_at` DATETIME NOT NULL) ENGINE=MyISAM');
        DB::statement("INSERT INTO `lab_requests` VALUES (1, '0000-00-00 00:00:00')");
        DB::statement('CREATE TABLE `medicine_availabilities` (
            `id` INT PRIMARY KEY, `availability_no` VARCHAR(191) NOT NULL, `created_at` DATETIME NOT NULL
        ) ENGINE=MyISAM');
        DB::statement("INSERT INTO `medicine_availabilities` VALUES (1, 'STOCK-001', '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$originalMode]);

        $migration = $this->loadMigration('2026_10_02_150000_lab_request_soft_delete_and_unique_stock_in_numbers.php');
        $migration->up();

        $this->assertNull(DB::table('lab_requests')->value('deleted_at'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('lab_requests')->value('created_at'));
        $this->assertSame('0000-00-00 00:00:00', DB::table('medicine_availabilities')->value('created_at'));
        $this->assertSame($originalMode, $this->sqlMode());
        $migration->down();
        $this->assertSame($originalMode, $this->sqlMode());
    }

    private function allowFixtureZeroDates(): string
    {
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
        $originalMode = $this->sqlMode();
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");

        return $originalMode;
    }

    private function foreignKeyDeleteRule(string $constraint): string
    {
        return DB::selectOne('SELECT DELETE_RULE AS delete_rule FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ?', [$constraint])->delete_rule;
    }

    private function migration(): \Illuminate\Database\Migrations\Migration
    {
        return $this->loadMigration('2026_09_30_121000_give_legacy_not_null_columns_defaults.php');
    }

    private function loadMigration(string $filename): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/'.$filename);
    }

    private function sqlMode(): string
    {
        return DB::selectOne('SELECT @@SESSION.sql_mode AS sql_mode', [], false)->sql_mode;
    }

    private function column(string $table, string $column): object
    {
        return DB::selectOne('SELECT IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default, DATA_TYPE AS data_type
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$table, $column]);
    }
}
