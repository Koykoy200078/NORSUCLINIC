<?php

namespace Tests\Feature\Regression;

use App\Services\DatabaseIntegrityChecker;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesFixtureDatabase;
use Tests\TestCase;

/**
 * Plan Phase 1.5: the structure checks of `php artisan db:integrity` (engines, foreign keys, dangling references, zero
 * dates, duplicate numbers, schema parity), each on a throwaway schema built to have exactly one problem.
 */
class DatabaseIntegritySchemaTest extends TestCase
{
    use UsesFixtureDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureDatabase('integrity_schema');
    }

    protected function tearDown(): void
    {
        try {
            $this->dropFixtureDatabase();
        } finally {
            parent::tearDown();
        }
    }

    public function test_a_myisam_table_is_an_error_and_innodb_tables_pass(): void
    {
        DB::statement('CREATE TABLE `modern` (`id` INT PRIMARY KEY) ENGINE=InnoDB');
        $this->assertSame('ok', $this->checker()->engines()['severity']);

        DB::statement('CREATE TABLE `legacy` (`id` INT PRIMARY KEY) ENGINE=MyISAM');
        $check = $this->checker()->engines();

        $this->assertSame('error', $check['severity']);
        $this->assertSame(['table legacy is MyISAM, not InnoDB'], $check['errors']);
    }

    public function test_a_missing_foreign_key_is_an_error_and_says_why(): void
    {
        DB::statement('CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `doctors` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB');
        DB::table('users')->insert(['id' => 1]);
        DB::table('doctors')->insert([['id' => 1, 'user_id' => 1], ['id' => 2, 'user_id' => 99]]);

        $check = $this->checker()->foreignKeys();

        $this->assertSame('error', $check['severity']);
        $errors = implode("\n", $check['errors']);
        $this->assertStringContainsString('doctors_user_id_foreign: 1 row(s) of doctors.user_id point at users rows that do not exist (values: 99', $errors);
        $this->assertStringContainsString('addresses_city_id_foreign: table addresses does not exist', $errors);
    }

    public function test_a_key_that_can_be_added_now_points_at_the_command_that_adds_it(): void
    {
        DB::statement('CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `doctors` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB');

        $errors = implode("\n", $this->checker()->foreignKeys()['errors']);

        $this->assertStringContainsString('doctors_user_id_foreign: missing, nothing blocks it - run php artisan db:restore-foreign-keys', $errors);
    }

    public function test_rows_pointing_at_a_record_that_does_not_exist_are_found_without_a_foreign_key(): void
    {
        DB::statement('CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `document_issuances` (
            `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY, `user_id` BIGINT UNSIGNED NULL, `nursing_incharged_id` BIGINT UNSIGNED NULL
        ) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `activity_logs` (`id` INT PRIMARY KEY, `user_id` BIGINT UNSIGNED NULL) ENGINE=InnoDB');
        DB::table('users')->insert(['id' => 1]);
        DB::table('document_issuances')->insert([
            ['id' => 1, 'user_id' => 1, 'nursing_incharged_id' => null],
            ['id' => 2, 'user_id' => 99, 'nursing_incharged_id' => null],
        ]);
        DB::table('activity_logs')->insert(['id' => 1, 'user_id' => 77]);   // the trail keeps the name of a deleted user: allowed

        $check = $this->checker()->danglingReferences();

        $this->assertSame('error', $check['severity']);
        $this->assertSame(['document_issuances.user_id: 1 row(s) point at users that do not exist (99)'], $check['errors']);
    }

    public function test_a_reference_covered_by_a_catalog_key_is_reported_once_by_the_key_check_only(): void
    {
        DB::statement('CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('CREATE TABLE `doctors` (`id` BIGINT UNSIGNED NOT NULL PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB');
        DB::table('doctors')->insert(['id' => 1, 'user_id' => 99]);

        $this->assertSame('ok', $this->checker()->danglingReferences()['severity']);
    }

    public function test_zero_dates_are_an_error_when_the_column_could_hold_null_and_a_warning_when_it_could_not(): void
    {
        $strictMode = $this->allowFixtureZeroDates();
        DB::statement('CREATE TABLE `visits` (`id` INT PRIMARY KEY, `seen_at` DATETIME NULL, `required_at` DATETIME NOT NULL) ENGINE=InnoDB');
        DB::statement("INSERT INTO `visits` VALUES (1, '0000-00-00 00:00:00', '0000-00-00 00:00:00')");
        DB::statement('SET SESSION sql_mode = ?', [$strictMode]);

        $check = $this->checker()->zeroDates();

        $this->assertSame('error', $check['severity']);
        $this->assertSame(['visits.seen_at: 1 zero date(s) - run php artisan migrate'], $check['errors']);
        $this->assertSame(['visits.required_at: 1 zero date(s) in a NOT NULL column - fix by hand'], $check['warnings']);
    }

    public function test_duplicate_business_numbers_are_an_error_when_the_unique_index_is_missing(): void
    {
        DB::statement('CREATE TABLE `settings` (`id` INT PRIMARY KEY, `key` VARCHAR(50) NULL) ENGINE=InnoDB');
        DB::table('settings')->insert([['id' => 1, 'key' => 'clinic_name'], ['id' => 2, 'key' => 'clinic_name'], ['id' => 3, 'key' => 'logo'], ['id' => 4, 'key' => null], ['id' => 5, 'key' => null]]);

        $check = $this->checker()->duplicateKeys();

        $this->assertSame('error', $check['severity']);
        $this->assertSame(['settings.key: "clinic_name" appears 2 times'], $check['errors'], 'two NULLs are not duplicates');
    }

    public function test_the_comparison_with_a_fresh_install_builds_and_drops_its_own_schema(): void
    {
        $differences = $this->checker()->compareWithFreshInstall();

        $joined = implode("\n", $differences);
        $this->assertStringContainsString('table patients is missing', $joined, 'the fixture schema is empty, a fresh install is not');
        $this->assertStringContainsString('table users is missing', $joined);
        $leftovers = DB::select("SELECT SCHEMA_NAME AS name FROM information_schema.SCHEMATA WHERE SCHEMA_NAME LIKE ?", ['%\_fresh\_compare'], false);
        $this->assertSame([], $leftovers, 'the throwaway fresh-install schema must be dropped');
    }

    public function test_the_comparison_refuses_to_replace_a_schema_that_already_has_its_name(): void
    {
        $scratch = substr(DB::connection()->getDatabaseName(), 0, 48).'_fresh_compare';
        DB::statement('CREATE DATABASE `'.$scratch.'`');
        DB::statement('CREATE TABLE `'.$scratch.'`.`precious` (`id` INT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('INSERT INTO `'.$scratch.'`.`precious` VALUES (1)');

        try {
            $this->checker()->compareWithFreshInstall();
            $this->fail('A schema that already exists must not be dropped and rebuilt.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString("A schema named {$scratch} already exists", $e->getMessage());
            $this->assertSame(1, (int) DB::selectOne('SELECT COUNT(*) AS n FROM `'.$scratch.'`.`precious`')->n, 'its data is untouched');
        } finally {
            DB::statement('DROP DATABASE IF EXISTS `'.$scratch.'`');
        }
    }

    private function checker(): DatabaseIntegrityChecker
    {
        return new DatabaseIntegrityChecker();
    }
}
