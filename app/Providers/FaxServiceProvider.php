<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\Efax;
use App\Services\Phaxio\PhaxioFaxService;
use Illuminate\Support\ServiceProvider;
use Phaxio;

class FaxServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    public function provides()
    {
        return [
            Efax::class,
        ];
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(Efax::class, function () {
            $config = config('services.phaxio');

            $phaxio = new Phaxio($config['key'], $config['secret'], $config['host']);

            return new PhaxioFaxService($phaxio);
        });
    }
}
