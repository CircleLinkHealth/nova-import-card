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
        $this->registerViews();
    }

    public function register()
    {
        $this->registerConfig();
        $this->app->register(RouteServiceProvider::class);
    }

    private function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('smartonfhirsso.php'),
            ],
            'config'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'smartonfhirsso'
        );
    }

    private function registerListeners()
    {
        $this->app['events']->listen(LoginEvent::class, LoginEventListener::class);
    }

    public function registerViews()
    {
        $viewPath = resource_path('views/modules/smartonfhirsso');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/smartonfhirsso';
        }, \Config::get('view.paths')), [$sourcePath]), 'smartonfhirsso');
    }
}
