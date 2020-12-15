<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Providers;


use CircleLinkHealth\Customer\Console\Commands\CreateLocationsFromAthenaApi;
use CircleLinkHealth\Customer\Console\Commands\CreateOrReplacePatientAWVSurveyInstanceStatusTable;
use CircleLinkHealth\Customer\Console\Commands\CreateRolesPermissionsMigration;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerConfig();
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

        $this->publishes([
            __DIR__ . '/../Config/medialibrary.php' => config_path('medialibrary.php'),
        ], 'medialibrary');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/medialibrary.php',
            'medialibrary'
        );
    }
}