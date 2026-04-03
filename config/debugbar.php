<?php

return [

    'enabled' => env('DEBUGBAR_ENABLED', null),

    'except' => [
        'telescope*',
        'horizon*',
    ],

    'storage' => [
        'enabled'    => true,
        'open'       => env('DEBUGBAR_OPEN_STORAGE', false),
        'driver'     => 'file',
        'path'       => storage_path('debugbar'),
        'connection' => null,
        'provider'   => '',
        'hostname'   => '127.0.0.1',
        'port'       => 2304,
    ],

    'collectors' => [
        'phpinfo'         => true,
        'messages'        => true,
        'time'            => true,
        'memory'          => true,
        'exceptions'      => true,
        'log'             => true,
        'db'              => true,
        'views'           => true,
        'route'           => true,
        'auth'            => false,
        'gate'            => true,
        'session'         => false,  // disabled — stops the duplicate SELECT * FROM sessions at end-of-request
        'symfony_request' => true,
        'mail'            => true,
        'laravel'         => false,
        'events'          => false,
        'default_request' => false,
        'logs'            => false,
        'files'           => false,
        'config'          => false,
        'cache'           => false,
        'models'          => true,
        'livewire'        => true,
    ],

    'options' => [
        'time'   => ['memory_usage' => false],
        'db'     => [
            'with_params'       => true,
            'backtrace'         => true,
            'backtrace_exclude_vendors' => false,
            'timeline'          => false,
            'duration_background'  => true,
            'explain'           => [
                'enabled' => false,
                'types'   => ['SELECT'],
            ],
            'hints'             => false,
            'show_copy'         => false,
            'slow_threshold'    => false,
        ],
        'mail'   => ['full_log' => false],
        'views'  => [
            'timeline'          => false,
            'data'              => false,
            'exclude_paths'     => [
                'vendor/filament',
            ],
        ],
        'route'  => ['label' => true],
        'logs'   => ['file' => null],
        'cache'  => ['values' => true],
    ],

    'inject' => true,

];
