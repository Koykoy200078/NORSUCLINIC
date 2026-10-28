<?php

/**
 * Test helper of RealtimeDiagnosticsTest: a tiny stand-in for Reverb, run as a child process.
 *
 *   php fake-reverb.php <port> <reverb|unauthorized|wrongkey|http|silent> [application key, default "the-key"]
 *
 *  reverb        WebSocket: upgrade + "pusher:connection_established"; API (POST /apps/.../events): 200
 *  unauthorized  the same WebSocket, but the API answers 401 (the application's signature is refused)
 *  broken        the same WebSocket, but the API answers 500
 *  wrongkey      WebSocket: upgrade + "pusher:error" 4001 (Reverb does not know the application key)
 *  http          anything: 404 (some other web server on the port)
 *  silent        accepts the connection and never answers
 *
 * It serves one connection after the other and ends by itself after a minute, so a forgotten test run leaves nothing behind.
 */
[, $port, $mode, $appKey] = $argv + [null, '0', 'reverb', 'the-key'];

$server = @stream_socket_server("tcp://127.0.0.1:$port", $errno, $error);
if (! $server) {
    fwrite(STDERR, "cannot listen on $port: $error\n");
    exit(1);
}

$frame = fn (string $payload): string => chr(0x81).chr(strlen($payload)).$payload; // payloads here are shorter than 126 bytes
$upgrade = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: x\r\n\r\n";
$json = fn (int $status, string $reason, string $body): string => "HTTP/1.1 $status $reason\r\nContent-Type: application/json\r\nContent-Length: ".strlen($body)."\r\nConnection: close\r\n\r\n$body";

$until = time() + 60;
while (time() < $until && ($client = @stream_socket_accept($server, 5))) {
    stream_set_timeout($client, 1);
    $request = (string) fread($client, 8192);
    $socket = str_starts_with($request, 'GET /app/');
    $api = str_starts_with($request, 'POST /apps/'); // publishing an event, what the application does
    // like Reverb: it only knows one application, the key is the path segment after /app/
    $keyOk = $socket && preg_match('#^GET /app/([^?\s]+)#', $request, $match) === 1 && $match[1] === $appKey;

    if ($mode === 'silent') {
        usleep(2500000);
    } elseif ($mode === 'http') {
        fwrite($client, "HTTP/1.1 404 Not Found\r\nContent-Length: 0\r\nConnection: close\r\n\r\n");
    } elseif ($socket && ($mode === 'wrongkey' || ! $keyOk)) {
        fwrite($client, $upgrade.$frame('{"event":"pusher:error","data":"{\"code\":4001,\"message\":\"Application does not exist\"}"}'));
    } elseif ($socket) {
        fwrite($client, $upgrade.$frame('{"event":"pusher:connection_established","data":"{}"}'));
    } elseif ($api) {
        fwrite($client, match ($mode) {
            'unauthorized' => $json(401, 'Unauthorized', '{}'),
            'broken' => $json(500, 'Internal Server Error', '{}'),
            default => $json(200, 'OK', '{}'),
        });
    }
    fclose($client);
}
