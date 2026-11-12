<?php

namespace Tests\Feature\Regression;

use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Backups screen runs mysqldump / mysql as child processes. Symfony Process hands a child only the variables
 * that also appear in $_SERVER. Under PHP's built-in server (`php artisan serve`, what the startup script runs)
 * with no config cache, $_SERVER holds the request fields and the keys Laravel loaded from .env, but none of
 * Windows' own variables: mysqldump started without SystemRoot / PATH and failed with "Got error: 2004: Can't create
 * TCP/IP socket (10106)", so "Create New Backup" and "Upload & Restore" failed (live re-audit, 2026-11-12). With
 * the config cached (the startup script runs `optimize`) the same request worked, which is why it went unnoticed.
 *
 * The dump runs for real against the throwaway test schema; nothing is mocked.
 */
class BackupChildEnvironmentTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $savedServer;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->savedServer = $_SERVER;
        $this->directory = storage_path('framework/testing/backup-child-env-' . bin2hex(random_bytes(4)));
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->savedServer;
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_a_backup_taken_in_a_web_request_without_a_config_cache_creates_a_complete_dump(): void
    {
        $this->serverVariablesOfABuiltInServerRequestWithoutConfigCache();

        $path = app(DatabaseBackupService::class)->createDump($this->directory, 'child-env');

        $this->assertFileExists($path);
        $this->assertStringContainsString('Dump completed', $this->tailOf($path));
    }

    public function test_a_restore_run_in_a_web_request_without_a_config_cache_loads_the_dump(): void
    {
        $service = app(DatabaseBackupService::class);
        $path = $service->createDump($this->directory, 'child-env-restore');

        $this->serverVariablesOfABuiltInServerRequestWithoutConfigCache();
        $service->restore($path);

        $this->assertTrue(Schema::hasTable('migrations'));
    }

    public function test_the_database_password_reaches_the_child_through_mysql_pwd(): void
    {
        $environment = app(DatabaseBackupService::class)->childEnvironment('secret-for-test');

        $this->assertSame('secret-for-test', $environment['MYSQL_PWD']);
    }

    /**
     * What $_SERVER holds in a request served by `php -S` when Laravel loads .env itself: request fields plus .env
     * keys (DB_DATABASE is one of them and is also in getenv()), and no SystemRoot / PATH.
     */
    private function serverVariablesOfABuiltInServerRequestWithoutConfigCache(): void
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/admin/backups/create',
            'DB_DATABASE' => (string) getenv('DB_DATABASE'),
            'APP_ENV' => (string) getenv('APP_ENV'),
        ];
    }

    private function tailOf(string $path): string
    {
        $size = filesize($path);
        $handle = fopen($path, 'rb');
        fseek($handle, max(0, $size - 512));
        $tail = (string) stream_get_contents($handle);
        fclose($handle);

        return $tail;
    }
}
