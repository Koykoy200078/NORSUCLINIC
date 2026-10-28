<?php

namespace Tests\Feature\Regression;

use App\Services\RealtimeDiagnostics;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * `php artisan realtime:check`: tells the person at the clinic PC WHICH link of the real-time chain is broken, instead of the
 * pages only saying "Live updates off" (owner report 2026-10-28). The socket code runs against a tiny fake Reverb in a child
 * process, so what is tested is the real handshake, not a mock.
 */
class RealtimeDiagnosticsTest extends TestCase
{
    /** @var array<int, resource> */
    private array $processes = [];

    protected function tearDown(): void
    {
        foreach ($this->processes as $process) {
            @proc_terminate($process);
            @proc_close($process);
        }
        parent::tearDown();
    }

    // ------------------------------------------------------------------ reading an answer (no network)

    public function test_a_reverb_welcome_is_recognised(): void
    {
        $this->assertSame('ready', RealtimeDiagnostics::interpret($this->upgrade().$this->frame('{"event":"pusher:connection_established","data":"{}"}'))['state']);
    }

    public function test_a_long_welcome_frame_with_an_extended_length_is_read_too(): void
    {
        $long = '{"event":"pusher:connection_established","data":"'.str_repeat('x', 300).'"}';
        $this->assertSame('ready', RealtimeDiagnostics::interpret($this->upgrade().$this->frame($long))['state']);
    }

    public function test_reverb_refusing_the_application_key_is_told_apart_from_a_missing_reverb(): void
    {
        $answer = RealtimeDiagnostics::interpret($this->upgrade().$this->frame('{"event":"pusher:error","data":"{\"code\":4001,\"message\":\"Application does not exist\"}"}'));

        $this->assertSame('wrong-key', $answer['state']);
        $this->assertStringContainsString('4001', $answer['detail']);
    }

    public function test_any_other_reverb_error_is_reported_as_an_error_not_as_a_wrong_key(): void
    {
        $answer = RealtimeDiagnostics::interpret($this->upgrade().$this->frame('{"event":"pusher:error","data":"{\"code\":4100,\"message\":\"Over capacity\"}"}'));

        $this->assertSame('error', $answer['state']);
        $this->assertStringContainsString('Over capacity', $answer['detail']);
    }

    public function test_something_that_is_not_reverb_is_recognised(): void
    {
        $this->assertSame('not-reverb', RealtimeDiagnostics::interpret("HTTP/1.1 404 Not Found\r\nContent-Length: 0\r\n\r\n")['state']);
        $this->assertSame('not-reverb', RealtimeDiagnostics::interpret("SSH-2.0-OpenSSH_9.0\r\n")['state']);
        $this->assertSame('not-reverb', RealtimeDiagnostics::interpret($this->upgrade().$this->frame('{"event":"something:else"}'))['state']);
        $this->assertSame('silent', RealtimeDiagnostics::interpret('')['state']);
        $this->assertSame('silent', RealtimeDiagnostics::interpret($this->upgrade())['state'], 'upgraded but never said hello');
    }

    // ------------------------------------------------------------------ the real socket

    public function test_nobody_listening_is_reported_at_once(): void
    {
        $started = microtime(true);
        $answer = (new RealtimeDiagnostics)->handshake('127.0.0.1', $this->freePort(), 'any-key');

        $this->assertSame('refused', $answer['state']);
        $this->assertLessThan(3, microtime(true) - $started);
    }

    public function test_the_handshake_reads_what_a_server_really_answers(): void
    {
        $diagnostics = new RealtimeDiagnostics;
        foreach (['reverb' => 'ready', 'wrongkey' => 'wrong-key', 'http' => 'not-reverb', 'silent' => 'silent'] as $mode => $expected) {
            $port = $this->fakeServer($mode);
            $this->assertSame($expected, $diagnostics->handshake('127.0.0.1', $port, 'the-key', 1.5)['state'], "fake server mode $mode");
        }

        // the key that is sent is the one that is checked (a Reverb started with other credentials refuses it)
        $this->assertSame('wrong-key', $diagnostics->handshake('127.0.0.1', $this->fakeServer('reverb'), 'another-key', 1.5)['state']);
    }

    public function test_this_pcs_own_addresses_are_ipv4_and_never_loopback_or_link_local(): void
    {
        foreach ((new RealtimeDiagnostics)->localAddresses() as $address) {
            $this->assertNotFalse(filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4), $address);
            $this->assertStringStartsNotWith('127.', $address);
            $this->assertStringStartsNotWith('169.254.', $address);
        }
    }

    // ------------------------------------------------------------------ the report and the command

    public function test_real_time_switched_off_in_env_is_the_first_thing_said(): void
    {
        config(['broadcasting.default' => 'null']);

        $this->artisan('realtime:check')
            ->expectsOutputToContain('BROADCAST_DRIVER is "null" in .env')
            ->doesntExpectOutputToContain('[ok]')
            ->assertExitCode(1);
    }

    public function test_an_empty_key_with_no_app_key_to_derive_it_from_is_reported(): void
    {
        config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.key' => '']);

        $this->artisan('realtime:check')->expectsOutputToContain('REVERB_APP_KEY')->assertExitCode(1);
    }

    public function test_a_reverb_that_is_not_running_is_reported_with_what_to_do(): void
    {
        $this->pointAtReverb($this->freePort());

        $this->artisan('realtime:check')
            ->expectsOutputToContain('Reverb is not running')
            ->assertExitCode(1);
    }

    public function test_a_reverb_that_refuses_the_applications_key_is_a_stale_one(): void
    {
        $this->pointAtReverb($this->fakeServer('wrongkey'));

        $this->artisan('realtime:check')->expectsOutputToContain('other settings')->assertExitCode(1);
    }

    public function test_another_program_on_the_reverb_port_is_reported_as_such(): void
    {
        $this->pointAtReverb($this->fakeServer('http'));

        $this->artisan('realtime:check')->expectsOutputToContain('another program')->assertExitCode(1);
    }

    public function test_reverb_listening_on_the_loopback_address_only_is_reported_because_no_other_device_could_connect(): void
    {
        $this->pointAtReverb($this->fakeServer('reverb'));
        $this->app->bind(RealtimeDiagnostics::class, fn () => new RealtimeDiagnostics(fn () => ['127.0.0.2']));

        $this->artisan('realtime:check')
            ->expectsOutputToContain('answers on 127.0.0.1 only')
            ->assertExitCode(1);
    }

    public function test_a_healthy_reverb_passes_and_the_application_can_publish_to_it(): void
    {
        $this->pointAtReverb($this->fakeServer('reverb'));
        $this->app->bind(RealtimeDiagnostics::class, fn () => new RealtimeDiagnostics(fn () => ['127.0.0.1']));

        $this->artisan('realtime:check')
            ->expectsOutputToContain('Reverb answers on 127.0.0.1')
            ->expectsOutputToContain('can publish')
            ->assertExitCode(0);
    }

    public function test_a_wrong_reverb_host_for_publishing_is_reported_with_the_setting_to_change(): void
    {
        // REVERB_HOST is where the APPLICATION sends its notices (this PC, 127.0.0.1). The WebSocket side is fine here.
        $this->pointAtReverb($this->fakeServer('reverb'));
        config(['broadcasting.connections.reverb.options.host' => '127.0.0.2']);
        $this->app->bind(RealtimeDiagnostics::class, fn () => new RealtimeDiagnostics(fn () => ['127.0.0.1']));

        $exit = Artisan::call('realtime:check');
        $output = Artisan::output();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('cannot reach Reverb\'s API at 127.0.0.2:', $output);
        $this->assertStringContainsString('REVERB_HOST', $output, 'the person is told which setting to look at');
    }

    public function test_an_error_answer_of_reverbs_api_is_reported_with_its_status(): void
    {
        $this->pointAtReverb($this->fakeServer('broken'));
        $this->app->bind(RealtimeDiagnostics::class, fn () => new RealtimeDiagnostics(fn () => ['127.0.0.1']));

        $this->artisan('realtime:check')->expectsOutputToContain('answered an error (500)')->assertExitCode(1);
    }

    public function test_reverb_refusing_the_applications_signature_is_reported_even_though_the_websocket_connects(): void
    {
        // The case that looks fine in the browser (connected) but never updates: the secret differs from the one Reverb runs with.
        $this->pointAtReverb($this->fakeServer('unauthorized'));
        $this->app->bind(RealtimeDiagnostics::class, fn () => new RealtimeDiagnostics(fn () => ['127.0.0.1']));

        $this->artisan('realtime:check')
            ->expectsOutputToContain('refused')
            ->assertExitCode(1);
    }

    public function test_the_command_is_registered(): void
    {
        $this->assertArrayHasKey('realtime:check', Artisan::all());
    }

    // ------------------------------------------------------------------ helpers

    private function pointAtReverb(int $port): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => $port,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
            'broadcasting.connections.reverb.key' => 'the-key',
            'broadcasting.connections.reverb.secret' => 'the-secret',
            'broadcasting.connections.reverb.app_id' => 'clinic',
        ]);
    }

    private function upgrade(): string
    {
        return "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: x\r\n\r\n";
    }

    /** One unmasked text frame, as a server sends it. */
    private function frame(string $payload): string
    {
        $length = strlen($payload);
        $head = $length < 126 ? chr(0x81).chr($length) : chr(0x81).chr(126).pack('n', $length);

        return $head.$payload;
    }

    private function freePort(): int
    {
        $probe = stream_socket_server('tcp://127.0.0.1:0');
        $name = stream_socket_get_name($probe, false);
        fclose($probe);

        return (int) substr($name, strrpos($name, ':') + 1);
    }

    /** Starts a fake Reverb in a child process and returns its port once it accepts connections. */
    private function fakeServer(string $mode): int
    {
        $port = $this->freePort();
        $script = __DIR__.'/fake-reverb.php';
        $process = proc_open([PHP_BINARY, $script, (string) $port, $mode], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        $this->processes[] = $process;
        for ($i = 0; $i < 60; $i++) {
            $client = @stream_socket_client("tcp://127.0.0.1:$port", $errno, $error, 0.2);
            if ($client) {
                fclose($client);

                return $port;
            }
            usleep(100000);
        }
        $this->fail("the fake Reverb ($mode) did not start");
    }
}
