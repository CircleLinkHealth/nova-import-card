<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\Efax;
use App\Services\Phaxio\PhaxioService;
use Illuminate\Support\ServiceProvider;
use Phaxio;

class FaxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(Efax::class, function () {
            $config = config('phaxio');

            $mode = isProductionEnv()
                ? 'production'
                : 'test';

            $phaxio = new Phaxio($config[$mode]['key'], $config[$mode]['secret'], $config['host']);

            return new PhaxioService($phaxio);
        });
    }
}
