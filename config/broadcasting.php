<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "reverb", "redis", "log", "null"
    |
    */

    // Honor both the Laravel 11 key (BROADCAST_CONNECTION) and the legacy L10 key
    // (BROADCAST_DRIVER) so the .env value is not silently ignored. CONFIG-4.
    'default' => env('BROADCAST_CONNECTION', env('BROADCAST_DRIVER', 'null')),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        // Laravel Reverb: the clinic's own WebSocket server (Phase 7), on the LAN; nothing leaves the building.
        // `host` is where the APPLICATION reaches Reverb to publish an event (the same PC: 127.0.0.1); the browsers connect
        // to the host of the page they came from (resources/assets/js/realtime). The short client timeouts make sure that a
        // stopped Reverb can never hold a save for more than a moment (and a failed publish is only logged, see
        // App\Support\Realtime).
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
                'path' => env('REVERB_SERVER_PATH', ''),
            ],
            'client_options' => [
                'timeout' => 2,
                'connect_timeout' => 1,
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
