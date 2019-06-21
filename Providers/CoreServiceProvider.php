<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use CircleLinkHealth\Core\Entities\DatabaseNotification as CircleLinkDatabaseNotification;
use CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel as CircleLinkDatabaseChannel;
use CircleLinkHealth\Core\Traits\HasDatabaseNotifications as CircleLinkHasDatabaseNotifications;
use CircleLinkHealth\Core\Traits\Notifiable as CircleLinkNotifiable;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Notifications\Channels\DatabaseChannel as LaravelDatabaseChannel;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications as LaravelHasDatabaseNotifications;
use Illuminate\Notifications\Notifiable as LaravelNotifiable;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

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
        $this->app->register(RouteServiceProvider::class);

        $this->app->bind(LaravelDatabaseChannel::class, CircleLinkDatabaseChannel::class);
        $this->app->bind(LaravelHasDatabaseNotifications::class, CircleLinkHasDatabaseNotifications::class);
        $this->app->bind(LaravelNotifiable::class, CircleLinkNotifiable::class);
        $this->app->bind(LaravelDatabaseNotification::class, CircleLinkDatabaseNotification::class);
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
        $langPath = resource_path('lang/modules/core');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'core');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/core');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/core';
        }, \Config::get('view.paths')), [$sourcePath]), 'core');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('core.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'core'
        );
    }
}
