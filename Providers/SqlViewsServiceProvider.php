<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews\Providers;

use CircleLinkHealth\SqlViews\Console\Commands\CreateSqlView;
use CircleLinkHealth\SqlViews\Console\Commands\MigrateSqlViews;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SqlViewsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            CreateSqlView::class,
            MigrateSqlViews::class,
        ];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->commands(
            [
                CreateSqlView::class,
                MigrateSqlViews::class,
            ]
        );
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('sqlviews.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'sqlviews'
        );
    }
}
