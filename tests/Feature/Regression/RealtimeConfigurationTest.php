<?php

namespace Tests\Feature\Regression;

use App\Support\RealtimeCredentials;
use Tests\TestCase;

/**
 * What a browser, the application and the Reverb server must agree on, whatever address the clinic PC has today.
 * (2026-10-28, owner report from the clinic: "Live updates off" although the page opened from the PC's LAN address.)
 */
class RealtimeConfigurationTest extends TestCase
{
    public function test_the_application_and_the_reverb_server_use_the_same_credentials_and_they_are_never_empty(): void
    {
        $broadcast = config('broadcasting.connections.reverb');
        $server = config('reverb.apps.apps.0');

        $this->assertNotSame('', (string) $broadcast['key'], 'an empty key switches real-time off silently');
        $this->assertNotSame('', (string) $broadcast['secret']);
        $this->assertNotSame('', (string) $broadcast['app_id']);
        $this->assertSame($broadcast['key'], $server['key']);
        $this->assertSame($broadcast['secret'], $server['secret']);
        $this->assertSame($broadcast['app_id'], $server['app_id']);
    }

    public function test_blank_credentials_in_the_environment_are_derived_from_the_app_key_in_both_configs(): void
    {
        [$broadcast, $server] = $this->withEnvironment(['REVERB_APP_ID' => '', 'REVERB_APP_KEY' => '', 'REVERB_APP_SECRET' => ''], fn () => [
            require config_path('broadcasting.php'),
            require config_path('reverb.php'),
        ]);

        $appKey = env('APP_KEY');
        $this->assertNotEmpty($appKey, 'the test environment has an APP_KEY');
        $expectedKey = RealtimeCredentials::key(null, $appKey);
        $this->assertSame($expectedKey, $broadcast['connections']['reverb']['key']);
        $this->assertSame($expectedKey, $server['apps']['apps'][0]['key']);
        $this->assertSame(RealtimeCredentials::secret(null, $appKey), $broadcast['connections']['reverb']['secret']);
        $this->assertSame(RealtimeCredentials::secret(null, $appKey), $server['apps']['apps'][0]['secret']);
        $this->assertSame('clinic', $broadcast['connections']['reverb']['app_id']);
        $this->assertSame('clinic', $server['apps']['apps'][0]['app_id']);
    }

    public function test_credentials_written_in_the_environment_win_over_the_derived_ones(): void
    {
        [$broadcast, $server] = $this->withEnvironment(['REVERB_APP_ID' => '7', 'REVERB_APP_KEY' => 'own-key', 'REVERB_APP_SECRET' => 'own-secret'], fn () => [
            require config_path('broadcasting.php'),
            require config_path('reverb.php'),
        ]);

        foreach ([$broadcast['connections']['reverb'], $server['apps']['apps'][0]] as $app) {
            $this->assertSame(['7', 'own-key', 'own-secret'], [$app['app_id'], $app['key'], $app['secret']]);
        }
    }

    /** Runs $callback with these variables set in every place `env()` reads from, then puts the previous state back. */
    private function withEnvironment(array $values, callable $callback)
    {
        $previous = [];
        foreach ($values as $name => $value) {
            $previous[$name] = [array_key_exists($name, $_ENV) ? $_ENV[$name] : null, array_key_exists($name, $_SERVER) ? $_SERVER[$name] : null, getenv($name)];
            $_ENV[$name] = $_SERVER[$name] = $value;
            putenv($name.'='.$value);
        }
        try {
            return $callback();
        } finally {
            foreach ($previous as $name => [$env, $server, $process]) {
                $env === null ? $this->forget($_ENV, $name) : $_ENV[$name] = $env;
                $server === null ? $this->forget($_SERVER, $name) : $_SERVER[$name] = $server;
                $process === false ? putenv($name) : putenv($name.'='.$process);
            }
        }
    }

    private function forget(array &$bag, string $name): void
    {
        unset($bag[$name]);
    }

    public function test_reverb_does_not_announce_the_loopback_address_the_application_publishes_to_as_its_own(): void
    {
        // REVERB_HOST is where the APPLICATION reaches Reverb (this PC). Reverb prints "(hostname)" after its address, so a
        // hostname of 127.0.0.1 read "Reverb runs on 127.0.0.1" although it listens on every address (0.0.0.0).
        $this->assertSame('0.0.0.0', config('reverb.servers.reverb.host'));
        $this->assertNull(config('reverb.servers.reverb.hostname'), 'REVERB_HOST must not be shown as the address Reverb runs on');
    }
}
