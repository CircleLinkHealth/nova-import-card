<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use CircleLinkHealth\Core\Contracts\Efax;
use CircleLinkHealth\Core\Services\Phaxio\PhaxioFaxService;
use CircleLinkHealth\Core\Services\Phaxio\PhaxioFaxServiceLogger;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Phaxio;

class FaxServiceProvider extends ServiceProvider implements DeferrableProvider
{
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
            $config = config('core.services.phaxio');

            $phaxio = new Phaxio($config['key'], $config['secret'], $config['host']);

            return new PhaxioFaxServiceLogger(new PhaxioFaxService($phaxio));
        });
    }
}
