<?php

namespace Tests\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * Builds a throwaway MySQL schema per test and makes it the default connection, so a test can create legacy-shaped
 * tables (MyISAM, zero dates, missing keys) and run a migration against them without touching the application's tables
 * or the shared test-suite schema. The schema name ends in "_test", which the destructive-database guard requires.
 *
 * Call useFixtureDatabase() in setUp() (after parent::setUp()) and dropFixtureDatabase() in tearDown().
 */
trait UsesFixtureDatabase
{
    private ?Connection $fixtureAdmin = null;

    private ?string $fixtureDatabase = null;

    private ?string $fixtureConnection = null;

    private string $fixtureOriginalConnection = '';

    /** @var list<string> */
    private array $extraFixtureDatabases = [];

    protected function useFixtureDatabase(string $prefix): void
    {
        $this->fixtureOriginalConnection = DB::getDefaultConnection();
        $this->fixtureAdmin = DB::connection();
        if ($this->fixtureAdmin->getDriverName() !== 'mysql') {
            $this->markTestSkipped('This regression requires MySQL.');
        }

        $this->fixtureDatabase = $prefix.'_'.bin2hex(random_bytes(6)).'_test';
        $this->fixtureConnection = 'fixture_'.$prefix;
        $this->fixtureAdmin->statement('CREATE DATABASE `'.$this->fixtureDatabase.'`');
        // The copied config carries "name" => "mysql". Eloquent re-resolves a new model's connection BY NAME
        // (firstOrCreate, create), so without its own name the inserts would land in the shared test schema.
        config(['database.connections.'.$this->fixtureConnection => array_merge(
            $this->fixtureAdmin->getConfig(),
            ['name' => $this->fixtureConnection, 'database' => $this->fixtureDatabase, 'url' => null, 'prefix' => '']
        )]);
        DB::setDefaultConnection($this->fixtureConnection);
    }

    /**
     * A second throwaway schema next to the fixture one (to compare two schemas); dropped by dropFixtureDatabase().
     * Tables are created in it with qualified names: CREATE TABLE `<name>`.`patients` (...).
     */
    protected function createExtraFixtureDatabase(): string
    {
        $name = 'extra_'.bin2hex(random_bytes(6)).'_test';
        $this->fixtureAdmin->statement('CREATE DATABASE `'.$name.'`');
        $this->extraFixtureDatabases[] = $name;

        return $name;
    }

    protected function dropFixtureDatabase(): void
    {
        if ($this->fixtureDatabase === null) {
            return;
        }

        DB::purge($this->fixtureConnection);
        DB::setDefaultConnection($this->fixtureOriginalConnection);
        foreach ([$this->fixtureDatabase, ...$this->extraFixtureDatabases] as $name) {
            $this->fixtureAdmin->statement('DROP DATABASE IF EXISTS `'.$name.'`');
        }
        $this->fixtureDatabase = null;
        $this->extraFixtureDatabases = [];
    }

    /**
     * The engine of a base table in the fixture schema, or null for a view / missing table.
     */
    protected function engineOf(string $table): ?string
    {
        return DB::selectOne(
            "SELECT ENGINE AS engine FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND TABLE_TYPE = 'BASE TABLE'",
            [$table],
            false
        )?->engine;
    }

    protected function sqlMode(): string
    {
        return DB::selectOne('SELECT @@SESSION.sql_mode AS sql_mode', [], false)->sql_mode;
    }

    protected function loadMigration(string $filename): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/'.$filename);
    }

    /**
     * Switch the session to the relaxed mode an old MySQL server used, so a fixture can hold zero dates; returns the
     * strict mode the application runs with (restore it with SET SESSION sql_mode afterwards).
     */
    protected function allowFixtureZeroDates(): string
    {
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
        $strictMode = $this->sqlMode();
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");

        return $strictMode;
    }
}
