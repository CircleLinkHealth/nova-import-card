<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Providers;

use CircleLinkHealth\Customer\Console\Commands\CreateLocationsFromAthenaApi;
use CircleLinkHealth\Customer\Console\Commands\CreateOrReplacePatientAWVSurveyInstanceStatusTable;
use CircleLinkHealth\Customer\Console\Commands\CreateRolesPermissionsMigration;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\ServiceProvider;

class CustomerDeferrableServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            CreateRolesPermissionsMigration::class,
            CreateOrReplacePatientAWVSurveyInstanceStatusTable::class,
            CreateLocationsFromAthenaApi::class,
            HasDatabaseNotifications::class,
            Notifiable::class,
            DatabaseNotification::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerFactories();
        if (class_exists(\App\User::class)) {
            Relation::morphMap([
                \CircleLinkHealth\Customer\Entities\User::class => \App\User::class,
            ]);
        }

        $this->app->bind(DatabaseNotification::class, \CircleLinkHealth\Core\Entities\DatabaseNotification::class);
        $this->app->bind(
            HasDatabaseNotifications::class,
            \CircleLinkHealth\Core\Traits\HasDatabaseNotifications::class
        );
        $this->app->bind(Notifiable::class, \CircleLinkHealth\Core\Traits\Notifiable::class);

        $this->commands([
            CreateRolesPermissionsMigration::class,
            CreateOrReplacePatientAWVSurveyInstanceStatusTable::class,
            CreateLocationsFromAthenaApi::class,
        ]);
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        app(Factory::class)->load(__DIR__.'/../Database/Factories');
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/customer');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'customer');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'customer');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/customer');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes(
            [
                $sourcePath => $viewPath,
            ],
            'views'
        );

        $this->loadViewsFrom(
            array_merge(
                array_map(
                    function ($path) {
                        return $path.'/modules/customer';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'customer'
        );
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('customer.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'customer'
        );

        $this->publishes(
            [
                __DIR__.'/../Config/cerberus.php' => config_path('cerberus.php'),
            ],
            'cerberus'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/cerberus.php',
            'cerberus'
        );
    }
}
