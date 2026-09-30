<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->guardAgainstDestructiveTestDatabase($app);

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

        $isMemorySqlite = $driver === 'sqlite' && $database === ':memory:';
        $isTestSchema = in_array($driver, ['mysql', 'mariadb'], true) && str_ends_with($database, '_test');

        if (! $isMemorySqlite && ! $isTestSchema) {
            fwrite(STDERR, "\n[ABORTED] Tests must run on an in-memory SQLite database or a MySQL/MariaDB schema whose "
                . "name ends in \"_test\". Refusing to run against connection '{$connection}' "
                . "(driver='{$driver}', database='{$database}') to protect production data. Check phpunit.xml / .env.\n");
            exit(1);
        }
    }
}
