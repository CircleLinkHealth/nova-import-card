<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\EpicSso\Providers;

use CircleLinkHealth\EpicSso\Events\EpicSsoLoginEvent;
use CircleLinkHealth\EpicSso\Listeners\EpicSsoLoginEventListener;
use Illuminate\Support\ServiceProvider;

class EpicSsoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerListeners();
    }

    public function register()
    {
        $this->registerConfig();
        $this->app->register(RouteServiceProvider::class);
    }

    private function registerConfig() {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('epicsso.php'),
            ],
            'config'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'epicsso'
        );
    }

    private function registerListeners()
    {
        $this->app['events']->listen(EpicSsoLoginEvent::class, EpicSsoLoginEventListener::class);
    }
}
