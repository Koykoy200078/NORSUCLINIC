<?php

namespace Tests\Feature\Regression;

use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * STATUS.md R3-M9: the clinic runs `php artisan serve`, which ignores public/.htaccess. The project's
 * server.php (picked up by artisan serve) must enforce the same protections itself: no script under
 * /uploads, no direct access to the legacy consultation photo folder, and no PHP file served from
 * public/ other than the front controller.
 *
 * This starts the real PHP built-in server with the project's router and asks it for files, so it checks
 * what the clinic PC will actually do. None of these requests reaches Laravel, so no database is used.
 */
class BuiltInServerRouterTest extends TestCase
{
    private ?Process $server = null;

    /** @var array<int, string> */
    private array $created = [];

    private int $port = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $probe = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        if (! $probe) {
            $this->markTestSkipped('no free local port: ' . $error);
        }
        $this->port = (int) substr(strrchr(stream_socket_get_name($probe, false), ':'), 1);
        fclose($probe);

        $this->file('uploads/_probe_shell.php', '<?php echo "EXECUTED";');
        $this->file('uploads/_probe_shell.PHTML', '<?php echo "EXECUTED";');
        $this->file('uploads/consultation_images/_probe_photo.jpg', 'private-photo');
        $this->file('uploads/_probe_ok.txt', 'plain upload');
        $this->file('_probe_script.php', '<?php echo "EXECUTED";');

        $this->server = new Process([PHP_BINARY, '-S', '127.0.0.1:' . $this->port, base_path('server.php')], public_path());
        $this->server->start();

        for ($i = 0; $i < 50; $i++) {
            $socket = @fsockopen('127.0.0.1', $this->port, $errno, $error, 0.2);
            if ($socket) {
                fclose($socket);

                return;
            }
            usleep(100000);
        }

        $this->markTestSkipped('the PHP built-in server did not start: ' . $this->server->getErrorOutput());
    }

    protected function tearDown(): void
    {
        $this->server?->stop(1);

        foreach (array_reverse($this->created) as $path) {
            @unlink($path);
        }
        @rmdir(public_path('uploads/consultation_images'));
        @rmdir(public_path('uploads'));

        parent::tearDown();
    }

    private function file(string $relative, string $content): void
    {
        $path = public_path($relative);
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $content);
        $this->created[] = $path;
    }

    /** @return array{0: int, 1: string} */
    private function fetch(string $path): array
    {
        $body = @file_get_contents('http://127.0.0.1:' . $this->port . $path, false, stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 10]]));
        $status = 0;
        foreach ($http_response_header ?? [] as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
                $status = (int) $m[1];
            }
        }

        return [$status, (string) $body];
    }

    public function test_scripts_in_the_uploads_folder_are_refused_and_never_run(): void
    {
        foreach (['/uploads/_probe_shell.php', '/uploads/_probe_shell.PHTML', '/uploads/%5Fprobe%5Fshell.php'] as $path) {
            [$status, $body] = $this->fetch($path);

            $this->assertSame(403, $status, $path);
            $this->assertStringNotContainsString('EXECUTED', $body, $path);
        }
    }

    public function test_the_legacy_consultation_photo_folder_is_not_served_directly(): void
    {
        [$status, $body] = $this->fetch('/uploads/consultation_images/_probe_photo.jpg');

        $this->assertSame(403, $status);
        $this->assertStringNotContainsString('private-photo', $body);
    }

    public function test_only_the_front_controller_may_run_as_a_script(): void
    {
        [$status, $body] = $this->fetch('/_probe_script.php');

        $this->assertSame(404, $status);
        $this->assertStringNotContainsString('EXECUTED', $body);
    }

    public function test_ordinary_files_are_still_served(): void
    {
        [$status, $body] = $this->fetch('/uploads/_probe_ok.txt');

        $this->assertSame(200, $status);
        $this->assertSame('plain upload', $body);
    }
}
