<?php

namespace App\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Foundation\Configuration\Console;

class ConsoleConfiguration extends Console
{
    public function boot(): void
    {
        AboutCommand::add('App', fn () => ['Version' => '1.0.0']);
        
        $this->commands([
            \Illuminate\Foundation\Console\MakeCommand::class,
            \Illuminate\Foundation\Console\NotificationMakeCommand::class,
        ]);
    }
}