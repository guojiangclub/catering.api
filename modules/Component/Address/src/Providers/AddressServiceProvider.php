<?php

namespace GuoJiangClub\Catering\Component\Address\Providers;

use Illuminate\Support\ServiceProvider;

class AddressServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }
    }
}