<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Providers;

use CircleLinkHealth\SmartOnFhirSso\Events\LoginEvent;
use CircleLinkHealth\SmartOnFhirSso\Listeners\LoginEventListener;
use Illuminate\Support\ServiceProvider;

class SmartOnFhirSsoServiceProvider extends ServiceProvider
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
                __DIR__.'/../Config/config.php' => config_path('smartonfhir.php'),
            ],
            'config'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'smartonfhir'
        );
    }

    private function registerListeners()
    {
        $this->app['events']->listen(LoginEvent::class, LoginEventListener::class);
    }
}
