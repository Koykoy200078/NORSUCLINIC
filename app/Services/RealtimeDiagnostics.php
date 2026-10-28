<?php

namespace App\Services;

use Pusher\ApiErrorException;
use Pusher\Pusher;

/**
 * Finds out which link of the real-time chain is broken, so the person at the clinic PC is told what to do instead of the
 * pages only saying "Live updates off" (php artisan realtime:check, also run by the startup script).
 *
 * The chain: BROADCAST_DRIVER and the credentials in the configuration -> a Reverb server that is running, is really Reverb
 * and knows this application's key -> that server answering on this PC's network addresses (not only on 127.0.0.1) -> the
 * application being allowed to publish to it (the secret matches). Whether the Windows firewall lets OTHER devices reach the
 * port cannot be seen from this PC and is checked by the startup script.
 *
 * Read-only: it opens connections and reads answers, it changes nothing.
 */
class RealtimeDiagnostics
{
    /** @var (callable(): array<int, string>)|null */
    private $addresses;

    /** @param (callable(): array<int, string>)|null $addresses replaces the detection of this PC's addresses (tests) */
    public function __construct(?callable $addresses = null)
    {
        $this->addresses = $addresses;
    }

    /**
     * @return array<int, array{level: string, text: string}> level: ok | problem | info
     */
    public function report(): array
    {
        $lines = [];
        $say = function (string $level, string $text) use (&$lines): void {
            $lines[] = ['level' => $level, 'text' => $text];
        };

        $driver = (string) config('broadcasting.default');
        if ($driver !== 'reverb') {
            $say('problem', "BROADCAST_DRIVER is \"$driver\" in .env: real-time is switched off and every page says \"Live updates off\". Set BROADCAST_DRIVER=reverb, then run php artisan config:clear.");

            return $lines;
        }
        $say('ok', 'BROADCAST_DRIVER is reverb');

        $connection = (array) config('broadcasting.connections.reverb');
        $key = (string) ($connection['key'] ?? '');
        if ($key === '' || (string) ($connection['secret'] ?? '') === '') {
            $say('problem', 'REVERB_APP_KEY / REVERB_APP_SECRET are empty and there is no APP_KEY to derive them from: run php artisan key:generate (or write them in .env), then php artisan config:clear.');

            return $lines;
        }
        $say('ok', 'The application key and secret are set');

        $port = (int) ($connection['options']['port'] ?? 8080);
        $local = $this->handshake('127.0.0.1', $port, $key);
        switch ($local['state']) {
            case 'ready':
                $say('ok', "Reverb answers on 127.0.0.1:$port (this application's key is accepted)");
                break;
            case 'refused':
                $say('problem', "Reverb is not running: nothing listens on 127.0.0.1:$port. Start the server with startup-dev-environment.bat (or php artisan reverb:start).");

                return $lines;
            case 'wrong-key':
                $say('problem', "Reverb on port $port refuses this application's key, so it was started with other settings (an older run, or another .env). Stop it and start the startup script again. ({$local['detail']})");

                return $lines;
            case 'not-reverb':
                $say('problem', "Port $port is used by another program, not Reverb ({$local['detail']}). Stop that program, or set another REVERB_PORT in .env (and allow that port in the firewall).");

                return $lines;
            default:
                $say('problem', "Port $port accepts connections but does not answer like Reverb ({$local['detail']}).");

                return $lines;
        }

        $addresses = $this->localAddresses();
        $answering = array_values(array_filter($addresses, fn (string $ip): bool => $this->handshake($ip, $port, $key)['state'] === 'ready'));
        if ($addresses === []) {
            $say('info', 'This PC has no network address (other than its own) right now: only this PC can connect.');
        } elseif ($answering === []) {
            $say('problem', 'Reverb answers on 127.0.0.1 only, not on '.implode(', ', $addresses).": it listens on the loopback address, so no other device can connect. Remove REVERB_SERVER_HOST from .env (or set it to 0.0.0.0) and restart Reverb.");

            return $lines;
        } else {
            $say('ok', 'Reverb also answers on '.implode(', ', array_map(fn (string $ip): string => "$ip:$port", $answering)));
        }

        $publish = $this->publishProblem($connection);
        if ($publish === null) {
            $say('ok', 'The application can publish notices to Reverb (key, secret and app id match)');
        } else {
            $say('problem', $publish);
        }
        $say('info', "Other devices also need TCP $port (and the port of this site) allowed in the Windows firewall of this PC: docs/commands.md, \"Real-time updates\".");

        return $lines;
    }

    /**
     * Opens a WebSocket to Reverb the way a browser does and says what answered.
     *
     * @return array{state: string, detail: string} state: ready | wrong-key | error | refused | not-reverb | silent
     */
    public function handshake(string $host, int $port, string $key, float $timeout = 2.0): array
    {
        $socket = @stream_socket_client("tcp://$host:$port", $errno, $error, $timeout);
        if (! $socket) {
            return ['state' => 'refused', 'detail' => trim($error) !== '' ? trim($error) : 'connection refused'];
        }

        stream_set_timeout($socket, (int) ceil($timeout));
        $nonce = base64_encode(random_bytes(16));
        fwrite($socket, "GET /app/$key?protocol=7&client=check&version=1.0&flash=false HTTP/1.1\r\n"
            ."Host: $host:$port\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Key: $nonce\r\nSec-WebSocket-Version: 13\r\nOrigin: http://$host\r\n\r\n");

        $raw = '';
        $until = microtime(true) + $timeout;
        while (microtime(true) < $until && ! feof($socket)) {
            $chunk = fread($socket, 4096);
            if ($chunk === false || $chunk === '') {
                if ((stream_get_meta_data($socket)['timed_out'] ?? false) === true) {
                    break;
                }
                usleep(20000);

                continue;
            }
            $raw .= $chunk;
            if (self::answerIsComplete($raw)) {
                break;
            }
        }
        fclose($socket);

        return self::interpret($raw);
    }

    /**
     * Reads what a server answered to the WebSocket request (no network).
     *
     * @return array{state: string, detail: string}
     */
    public static function interpret(string $raw): array
    {
        if ($raw === '') {
            return ['state' => 'silent', 'detail' => 'it accepted the connection but sent nothing'];
        }
        if (! preg_match('#^HTTP/1\.[01] (\d{3})#', $raw, $status)) {
            return ['state' => 'not-reverb', 'detail' => 'it does not speak HTTP'];
        }
        if ($status[1] !== '101') {
            return ['state' => 'not-reverb', 'detail' => "it answered HTTP {$status[1]} instead of a WebSocket upgrade"];
        }

        $payload = self::firstFramePayload(self::body($raw));
        if ($payload === null) {
            return ['state' => 'silent', 'detail' => 'it accepted the WebSocket upgrade but did not say hello'];
        }

        $message = json_decode($payload, true);
        $event = is_array($message) ? (string) ($message['event'] ?? '') : '';
        if ($event === 'pusher:connection_established') {
            return ['state' => 'ready', 'detail' => ''];
        }
        if ($event === 'pusher:error') {
            $data = $message['data'] ?? [];
            $data = is_string($data) ? (json_decode($data, true) ?: []) : (array) $data;
            $code = $data['code'] ?? '?';
            $text = (string) ($data['message'] ?? '');

            return ['state' => (int) $code === 4001 ? 'wrong-key' : 'error', 'detail' => "Reverb answered error $code".($text !== '' ? ": $text" : '')];
        }

        return ['state' => 'not-reverb', 'detail' => 'it upgraded to a WebSocket but is not Reverb'];
    }

    /**
     * This PC's IPv4 addresses on the networks that are up, without the loopback and link-local (169.254.x.x) ones.
     *
     * @return array<int, string>
     */
    public function localAddresses(): array
    {
        if ($this->addresses !== null) {
            return array_values(($this->addresses)());
        }

        $found = [];
        $interfaces = function_exists('net_get_interfaces') ? (@net_get_interfaces() ?: []) : [];
        foreach ($interfaces as $interface) {
            if (empty($interface['up'])) {
                continue;
            }
            foreach ($interface['unicast'] ?? [] as $unicast) {
                $ip = (string) ($unicast['address'] ?? '');
                if (($unicast['family'] ?? null) === AF_INET
                    && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
                    && ! str_starts_with($ip, '127.') && ! str_starts_with($ip, '169.254.')) {
                    $found[] = $ip;
                }
            }
        }

        return array_values(array_unique($found));
    }

    /** The reason the application cannot publish a notice to Reverb, or null when it can. */
    private function publishProblem(array $connection): ?string
    {
        $options = (array) ($connection['options'] ?? []);
        $where = ($options['host'] ?? '127.0.0.1').':'.($options['port'] ?? 8080);
        try {
            // The same signed request the application sends for every real notice, to a channel nobody listens to (Reverb's
            // channel listing is no use here: with no browser connected it answers an empty list the SDK cannot read).
            (new Pusher((string) $connection['key'], (string) $connection['secret'], (string) $connection['app_id'], $options + ['timeout' => 3]))
                ->trigger('realtime-check', 'RealtimeCheck', ['at' => time()]);

            return null;
        } catch (ApiErrorException $e) {
            if (in_array($e->getCode(), [401, 403], true)) {
                return "Reverb refused the application's signature ({$e->getCode()}): the secret differs from the one the running Reverb uses, so no notice reaches the pages (they look connected and never update). Stop Reverb and start the startup script again.";
            }

            if ($e->getCode() >= 400) {
                return "Reverb's API at $where answered an error ({$e->getCode()}) to the application: ".$e->getMessage();
            }

            // no HTTP status at all: the connection itself failed
            return "The application cannot reach Reverb's API at $where (REVERB_HOST / REVERB_PORT in .env): ".$e->getMessage();
        } catch (\Throwable $e) {
            return "The application cannot reach Reverb's API at $where (REVERB_HOST / REVERB_PORT in .env): ".class_basename($e).': '.$e->getMessage();
        }
    }

    /** Whether enough has been read to judge the answer: not an upgrade at all, or the upgrade and the first frame. */
    private static function answerIsComplete(string $raw): bool
    {
        if (strpos($raw, "\r\n\r\n") === false) {
            return strlen($raw) >= 5 && ! str_starts_with($raw, 'HTTP/');
        }
        if (! preg_match('#^HTTP/1\.[01] 101#', $raw)) {
            return true;
        }

        return self::firstFramePayload(self::body($raw)) !== null;
    }

    private static function body(string $raw): string
    {
        $split = strpos($raw, "\r\n\r\n");

        return $split === false ? '' : substr($raw, $split + 4);
    }

    /** The payload of the first (unmasked, server to client) WebSocket frame, or null while it is not complete. */
    private static function firstFramePayload(string $bytes): ?string
    {
        if (strlen($bytes) < 2) {
            return null;
        }
        $length = ord($bytes[1]) & 0x7F;
        $offset = 2;
        if ($length === 126) {
            if (strlen($bytes) < 4) {
                return null;
            }
            $length = unpack('n', substr($bytes, 2, 2))[1];
            $offset = 4;
        } elseif ($length === 127) {
            if (strlen($bytes) < 10) {
                return null;
            }
            $length = unpack('J', substr($bytes, 2, 8))[1];
            $offset = 10;
        }

        return strlen($bytes) < $offset + $length ? null : substr($bytes, $offset, $length);
    }
}
