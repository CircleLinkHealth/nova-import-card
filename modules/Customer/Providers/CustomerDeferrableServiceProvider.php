<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Providers;

use CircleLinkHealth\Customer\Console\Commands\CreateLocationsFromAthenaApi;
use CircleLinkHealth\Customer\Console\Commands\CreateOrReplacePatientAWVSurveyInstanceStatusTable;
use CircleLinkHealth\Customer\Console\Commands\CreateRolesPermissionsMigration;
use CircleLinkHealth\Customer\Console\Commands\EraseTestEnrollees;
use CircleLinkHealth\Customer\Console\Commands\ProcessPostmarkInboundMailCommand;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
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
            ProcessPostmarkInboundMailCommand::class,
            EraseTestEnrollees::class,
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
            ProcessPostmarkInboundMailCommand::class,
            EraseTestEnrollees::class,
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
     * Register config.
     */
    protected function registerConfig()
    {
    }
}
