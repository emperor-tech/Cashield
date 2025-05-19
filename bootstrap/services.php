<?php

return [
    'providers' => [
        App\Providers\AppServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    ],

    'bindings' => [
        Illuminate\Contracts\Debug\ExceptionHandler::class => App\Exceptions\Handler::class,
        Illuminate\Contracts\Console\Kernel::class => App\Console\Kernel::class,
    ],

    'singletons' => [
        'files' => \Illuminate\Filesystem\Filesystem::class,
    ],
];