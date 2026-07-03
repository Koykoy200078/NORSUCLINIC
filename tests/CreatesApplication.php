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
     * Safety net: refuse to run the suite against anything but an in-memory SQLite DB.
     *
     * RefreshDatabase runs migrate:fresh in setUp(), so if a real MySQL connection
     * (e.g. the live norsu_clinic database) ever leaks in via .env, this aborts the
     * process BEFORE a single table is dropped. Runs before any trait migration.
     */
    protected function guardAgainstDestructiveTestDatabase(Application $app): void
    {
        $connection = (string) $app['config']->get('database.default');
        $driver = (string) $app['config']->get("database.connections.{$connection}.driver");

        if ($driver !== 'sqlite') {
            $database = (string) $app['config']->get("database.connections.{$connection}.database");
            fwrite(STDERR, "\n[ABORTED] Tests must run on an in-memory SQLite database. Refusing to run "
                . "against connection '{$connection}' (driver='{$driver}', database='{$database}') "
                . "to protect production data. Check phpunit.xml / .env.\n");
            exit(1);
        }
    }
}
