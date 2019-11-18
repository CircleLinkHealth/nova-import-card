<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews\Providers;

use CircleLinkHealth\SqlViews\Console\Commands\CreateSqlView;
use CircleLinkHealth\SqlViews\Console\Commands\MigrateSqlViews;
use Illuminate\Support\ServiceProvider;

class SqlViewsServiceProvider extends ServiceProvider
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
    }

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
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    CreateSqlView::class,
                    MigrateSqlViews::class,
                ]
            );
        }
    }
}
