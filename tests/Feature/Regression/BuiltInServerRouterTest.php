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

    private ?string $fixtureRoot = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Unique roots avoid Windows file locks from the previous server process.
        $this->fixtureRoot = storage_path('framework/testing/router_'.bin2hex(random_bytes(6)));
        $this->app->usePublicPath($this->fixtureRoot.'/public');

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
        $this->file('uploads/_probe+plus.txt', 'plus upload');
        $this->file('_probe_script.php', '<?php echo "EXECUTED";');
        $this->file('index.php', '<?php http_response_code(404); echo "Not Found";');
        $this->file('../.env', "APP_KEY=fixture-only\nDB_PASSWORD=fixture-only\n");

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
        if ($this->fixtureRoot !== null) {
            @rmdir(public_path('uploads/consultation_images'));
            @rmdir(public_path('uploads'));
            @rmdir(public_path('consultation_images'));
            @rmdir(public_path());
            @rmdir($this->fixtureRoot);
        }

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

    public function test_windows_short_folder_names_cannot_reach_the_private_photo_folder(): void
    {
        $path = '/uploads/CONSUL~1/_probe_photo.jpg';
        if (PHP_OS_FAMILY !== 'Windows' || ! is_file(public_path(ltrim($path, '/')))) {
            $this->markTestSkipped('This filesystem does not expose the NTFS short name for consultation_images.');
        }

        [$status, $body] = $this->fetch($path);

        $this->assertSame(403, $status);
        $this->assertStringNotContainsString('private-photo', $body);
    }

    public function test_a_leading_double_slash_cannot_change_which_path_is_checked(): void
    {
        // A mirror makes the incorrectly parsed path an ordinary existing file.
        $this->file('consultation_images/_probe_photo.jpg', 'public-mirror');

        [$status, $body] = $this->fetch('//uploads/consultation_images/_probe_photo.jpg');

        $this->assertSame(403, $status);
        $this->assertStringNotContainsString('private-photo', $body);
    }

    public function test_ntfs_directory_stream_names_cannot_reach_the_private_photo_folder(): void
    {
        $path = '/uploads/consultation_images:$I30:$INDEX_ALLOCATION/_probe_photo.jpg';
        if (PHP_OS_FAMILY !== 'Windows' || ! is_file(public_path(ltrim($path, '/')))) {
            $this->markTestSkipped('This filesystem does not expose the NTFS directory stream alias.');
        }

        [$status, $body] = $this->fetch($path);

        $this->assertSame(403, $status);
        $this->assertStringNotContainsString('private-photo', $body);
    }

    /**
     * The checks used to run on the path exactly as typed, but the server resolves "." and ".." (and Windows ignores
     * trailing dots / spaces on every folder name) before it serves the file, so a path that merely LOOKED different
     * reached the private photos. Each of these must be refused, none may return the photo.
     */
    public function test_dot_segments_and_odd_folder_names_cannot_reach_the_private_photo_folder(): void
    {
        $paths = [
            '/uploads/../uploads/consultation_images/_probe_photo.jpg',
            '/uploads/./consultation_images/_probe_photo.jpg',
            '/uploads/consultation_images/../consultation_images/_probe_photo.jpg',
            '/x/../uploads/consultation_images/_probe_photo.jpg',
            '/uploads/%2e%2e/uploads/consultation_images/_probe_photo.jpg',
            '/uploads/%2e/consultation_images/_probe_photo.jpg',
            '/uploads//consultation_images//_probe_photo.jpg',
            '/uploads\\consultation_images\\_probe_photo.jpg',
            '/uploads/consultation_images./_probe_photo.jpg',
            '/uploads/consultation_images%20/_probe_photo.jpg',
            '/UPLOADS/Consultation_Images/_probe_photo.jpg',
        ];

        $leaks = [];
        foreach ($paths as $path) {
            [$status, $body] = $this->fetch($path);
            if (str_contains($body, 'private-photo') || $status === 200) {
                $leaks[] = "{$path} -> {$status}";
            }
        }

        $this->assertSame([], $leaks, 'the private photo folder was reachable through these paths');
    }

    public function test_the_project_configuration_file_cannot_be_reached_by_climbing_out_of_public(): void
    {
        foreach (['/../.env', '/uploads/../../.env', '/%2e%2e/.env', '/..%2f.env', '/uploads/%2e%2e/%2e%2e/.env', '/..\\.env'] as $path) {
            [, $body] = $this->fetch($path);

            $this->assertStringNotContainsString('APP_KEY', $body, $path);
            $this->assertStringNotContainsString('DB_PASSWORD', $body, $path);
        }
    }

    public function test_dot_segments_cannot_slip_a_script_past_the_uploads_rule(): void
    {
        foreach (['/uploads/../uploads/_probe_shell.php', '/uploads/./_probe_shell.php', '/uploads/%2e/_probe_shell.PHTML'] as $path) {
            [, $body] = $this->fetch($path);

            $this->assertStringNotContainsString('EXECUTED', $body, $path);
        }
    }

    public function test_only_the_front_controller_may_run_as_a_script(): void
    {
        [$status, $body] = $this->fetch('/_probe_script.php');

        $this->assertSame(404, $status);
        $this->assertStringNotContainsString('EXECUTED', $body);
    }

    public function test_ordinary_files_are_still_served(): void
    {
        foreach (['/uploads/_probe_ok.txt' => 'plain upload', '/uploads/_probe+plus.txt' => 'plus upload', '/uploads/_probe%2Bplus.txt' => 'plus upload'] as $path => $expected) {
            [$status, $body] = $this->fetch($path);

            $this->assertSame(200, $status, $path);
            $this->assertSame($expected, $body, $path);
        }
    }
}
