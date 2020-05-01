<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DevelopmentServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
    }

    public function provides()
    {
        return [
            \Orangehill\Iseed\IseedServiceProvider::class,
            \Laravel\Dusk\DuskServiceProvider::class,
            \JKocik\Laravel\Profiler\ServiceProvider::class,
        ];
    }

    /**
     * Register services.
     */
    public function register()
    {
        $this->app->register(\Orangehill\Iseed\IseedServiceProvider::class);
        $this->app->register(\Laravel\Dusk\DuskServiceProvider::class);
        $this->app->register(\JKocik\Laravel\Profiler\ServiceProvider::class);
    }
}
