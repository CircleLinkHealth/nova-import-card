<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Providers;

use Illuminate\Support\ServiceProvider;

class TwoFAServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerVueComponents();

        $this->registerViews();

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/twofa');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/twofa';
        }, \Config::get('view.paths')), [$sourcePath]), 'twofa');
    }

    public function registerVueComponents()
    {
        $this->publishes([
            __DIR__.'/../Resources/assets/js/components' => resource_path(
                'assets/js/components'
            ), ], 'vue-components');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('twofa.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'twofa'
        );
    }
}
