<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Notifications\PatientUnsuccessfulCallNotification;
use CircleLinkHealth\SharedModels\Notifications\PatientUnsuccessfulCallReplyNotification;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerConfig();

        Relation::morphMap([
            Call::class                                     => 'App\Call',
            PatientUnsuccessfulCallNotification::class      => 'App\Notifications\PatientUnsuccessfulCallNotification',
            PatientUnsuccessfulCallReplyNotification::class => 'App\Notifications\PatientUnsuccessfulCallReplyNotification',
            User::class                                     => 'App\User',
        ]);

        $this->registerViews();
        $this->registerFactories();

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

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('core.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/../Config/live-notifications.php' => config_path('live-notifications.php'),
        ], 'live-notifications');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'core'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/live-notifications.php',
            'live-notifications'
        );
    }
}
