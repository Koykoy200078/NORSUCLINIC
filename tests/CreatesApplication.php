<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Throwable;

trait CreatesApplication
{
    /**
     * Set once the dedicated test schema is known to exist, so the check runs once per PHP process.
     */
    private static bool $testSchemaEnsured = false;

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->guardAgainstDestructiveTestDatabase($app);

        if (! self::$testSchemaEnsured) {
            $this->ensureTestSchemaExists($app);
            self::$testSchemaEnsured = true;
        }

        return $app;
    }

    /**
     * Safety net: refuse to run the suite against anything but a dedicated test database.
     *
     * RefreshDatabase runs migrate:fresh in setUp(), which DROPS every table. If the live clinic
     * database ever leaks in via .env, this aborts the process BEFORE a single table is dropped. The
     * database must be an in-memory SQLite one or a MySQL/MariaDB schema whose name ends in "_test".
     * Runs before any trait migration.
     */
    protected function guardAgainstDestructiveTestDatabase(Application $app): void
    {
        $connection = (string) $app['config']->get('database.default');
        $driver = (string) $app['config']->get("database.connections.{$connection}.driver");
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if (! $this->isSafeTestDatabase($driver, $database)) {
            fwrite(STDERR, "\n[ABORTED] Tests must run on an in-memory SQLite database or a MySQL/MariaDB schema whose "
                . "name ends in \"_test\". Refusing to run against connection '{$connection}' "
                . "(driver='{$driver}', database='{$database}') to protect production data. Check phpunit.xml / .env.\n");
            exit(1);
        }
    }

    /**
     * An in-memory SQLite database, or a MySQL/MariaDB schema named with plain word characters and ending in "_test".
     */
    protected function isSafeTestDatabase(string $driver, string $database): bool
    {
        if ($driver === 'sqlite') {
            return $database === ':memory:';
        }

        return in_array($driver, ['mysql', 'mariadb'], true)
            && preg_match('/^[A-Za-z0-9_]+_test$/', $database) === 1;
    }

    /**
     * Create the dedicated test schema when it does not exist yet, so a new machine needs no manual
     * "CREATE DATABASE". It only ever acts on a name that passed the guard, so a live clinic schema is never
     * created or changed here, and a schema that already exists is left alone.
     */
    protected function ensureTestSchemaExists(Application $app, ?string $connection = null): void
    {
        $connection ??= (string) $app['config']->get('database.default');
        $settings = (array) $app['config']->get("database.connections.{$connection}");
        $driver = (string) ($settings['driver'] ?? '');
        $database = (string) ($settings['database'] ?? '');

        if (! in_array($driver, ['mysql', 'mariadb'], true) || ! $this->isSafeTestDatabase($driver, $database)) {
            return;
        }

        try {
            $app['db']->connection($connection)->getPdo();

            return;
        } catch (Throwable $error) {
            if ($error->getCode() !== 1049 && ! str_contains($error->getMessage(), 'Unknown database')) {
                throw $error;
            }
        }

        // Connect to the server without selecting a schema, create the missing one, then drop the failed
        // connection so the next use reconnects to the new schema.
        $server = "{$connection}_server_only";
        $app['config']->set("database.connections.{$server}", array_merge($settings, ['database' => null, 'url' => null]));

        try {
            $app['db']->connection($server)->getSchemaBuilder()->createDatabase($database);
        } finally {
            $app['db']->purge($server);
            $app['db']->purge($connection);
        }
    }
}
