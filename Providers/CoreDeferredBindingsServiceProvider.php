<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use CircleLinkHealth\Core\Console\Commands\CreateAndSeedTestSuiteDB;
use CircleLinkHealth\Core\Console\Commands\CreateMySqlDB;
use CircleLinkHealth\Core\Console\Commands\CreatePostgreSQLDB;
use CircleLinkHealth\Core\Console\Commands\HerokuOnRelease;
use CircleLinkHealth\Core\Console\Commands\PostDeploymentTasks;
use CircleLinkHealth\Core\Console\Commands\ReviewAppCreateDb;
use CircleLinkHealth\Core\Console\Commands\ReviewAppPreDestroy;
use CircleLinkHealth\Core\Console\Commands\ReviewAppSeedDb;
use CircleLinkHealth\Core\Console\Commands\RunScheduler;
use CircleLinkHealth\Core\Console\Commands\StoreJiraTicketsDeployed;
use CircleLinkHealth\Core\Console\Commands\StoreRelease;
use CircleLinkHealth\Core\Entities\DatabaseNotification as CircleLinkDatabaseNotification;
use CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel as CircleLinkDatabaseChannel;
use CircleLinkHealth\Core\Traits\HasDatabaseNotifications as CircleLinkHasDatabaseNotifications;
use CircleLinkHealth\Core\Traits\Notifiable as CircleLinkNotifiable;
use CircleLinkHealth\Core\Vapor\DevOpsHelpers\Commands\DeleteAllSecrets;
use CircleLinkHealth\Core\Vapor\DevOpsHelpers\Commands\SyncEnvFiles;
use CircleLinkHealth\Core\Vapor\DevOpsHelpers\Commands\UploadSecretsFromFile;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Notifications\Channels\DatabaseChannel as LaravelDatabaseChannel;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications as LaravelHasDatabaseNotifications;
use Illuminate\Notifications\Notifiable as LaravelNotifiable;
use Illuminate\Support\ServiceProvider;

class CoreDeferredBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            LaravelDatabaseChannel::class,
            LaravelHasDatabaseNotifications::class,
            LaravelNotifiable::class,
            LaravelDatabaseNotification::class,
            CircleLinkDatabaseChannel::class,
            CircleLinkHasDatabaseNotifications::class,
            CircleLinkNotifiable::class,
            CircleLinkDatabaseNotification::class,
            CreateMySqlDB::class,
            CreatePostgreSQLDB::class,
            HerokuOnRelease::class,
            PostDeploymentTasks::class,
            ReviewAppCreateDb::class,
            ReviewAppPreDestroy::class,
            ReviewAppSeedDb::class,
            RunScheduler::class,
            StoreJiraTicketsDeployed::class,
            StoreRelease::class,
            CreateAndSeedTestSuiteDB::class,
            UploadSecretsFromFile::class,
            DeleteAllSecrets::class,
            SyncEnvFiles::class
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $arr = [
            CreateMySqlDB::class,
            CreatePostgreSQLDB::class,
            HerokuOnRelease::class,
            PostDeploymentTasks::class,
            ReviewAppCreateDb::class,
            ReviewAppPreDestroy::class,
            ReviewAppSeedDb::class,
            RunScheduler::class,
            StoreJiraTicketsDeployed::class,
            StoreRelease::class,
            SyncEnvFiles::class,
        ];

        if ($this->app->environment('testing')) {
            $arr[] = CreateAndSeedTestSuiteDB::class;
        }

        if ($this->app->environment('local')) {
            $arr[] = DeleteAllSecrets::class;
            $arr[] = UploadSecretsFromFile::class;
            $arr[] = SyncEnvFiles::class;
        }

        $this->commands($arr);

        $this->app->bind(LaravelDatabaseChannel::class, CircleLinkDatabaseChannel::class);
        $this->app->bind(LaravelHasDatabaseNotifications::class, CircleLinkHasDatabaseNotifications::class);
        $this->app->bind(LaravelNotifiable::class, CircleLinkNotifiable::class);
        $this->app->bind(LaravelDatabaseNotification::class, CircleLinkDatabaseNotification::class);
    }
}
