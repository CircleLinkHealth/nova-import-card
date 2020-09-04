<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class TimeTrackingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        if ( ! isProductionEnv()) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/timetracking');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'timetracking');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'timetracking');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/timetracking');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/timetracking';
        }, \Config::get('view.paths')), [$sourcePath]), 'timetracking');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('timetracking.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'timetracking'
        );
    }
}
