<?php

namespace Mitoop\LaravelTools;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Mitoop\LaravelTools\Commands\GenerateFillable;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateFillable::class,
            ]);
        }
    }
}
