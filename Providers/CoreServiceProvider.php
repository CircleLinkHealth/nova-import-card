<?php

namespace CircleLinkHealth\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;
use \CircleLinkHealth\Core\Entities\DatabaseNotification as CircleLinkDatabaseNotification;
use Illuminate\Notifications\Channels\DatabaseChannel as LaravelDatabaseChannel;
use Illuminate\Notifications\HasDatabaseNotifications as LaravelHasDatabaseNotifications;
use Illuminate\Notifications\Notifiable as LaravelNotifiable;
use \CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel as CircleLinkDatabaseChannel;
use \CircleLinkHealth\Core\Traits\HasDatabaseNotifications as CircleLinkHasDatabaseNotifications;
use \CircleLinkHealth\Core\Traits\Notifiable as CircleLinkNotifiable;

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
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
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
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('core.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'core'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/core');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/core';
        }, \Config::get('view.paths')), [$sourcePath]), 'core');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/core');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'core');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'core');
        }
    }

    /**
     * Register an additional directory of factories.
     * 
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
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
}
