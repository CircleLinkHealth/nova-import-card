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
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
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
        ];
    }
    
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerViews();
        $this->registerConfig();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        
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
        ];

        if ($this->app->environment('testing')) {
            $arr[] = CreateAndSeedTestSuiteDB::class;
        }

        $this->commands($arr);
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

        $this->publishes([
            __DIR__.'/../Config/live-notifications.php' => config_path('live-notifications.php'),
        ], 'live-notifications');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/live-notifications.php',
            'live-notifications'
        );
    }
}
