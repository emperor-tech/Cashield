<?php

return [
    'default' => env('BROADCAST_DRIVER', 'pusher'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY', 'local'),
            'secret' => env('PUSHER_APP_SECRET', 'local'),
            'app_id' => env('PUSHER_APP_ID', 'local'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'host' => '127.0.0.1',
                'port' => 8080,
                'scheme' => 'http',
                'encrypted' => false,
                'useTLS' => false,
            ],
        ],
        'reverb' => [
            'driver' => 'reverb',
            'app_id' => env('REVERB_APP_ID', ''),
            'key' => env('REVERB_APP_KEY', ''),
            'secret' => env('REVERB_APP_SECRET', ''),
            'path' => env('REVERB_PATH', 'reverb'),
            'host' => env('REVERB_SERVER', 'localhost'),
            'port' => env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
            'options' => [
                'cluster' => env('REVERB_CLUSTER', 'local'),
                'encrypted' => true,
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
            ],
        ],
    ],
];