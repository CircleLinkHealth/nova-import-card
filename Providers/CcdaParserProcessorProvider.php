<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcdaParserProcessorPhp\Providers;

use CircleLinkHealth\CcdaParserProcessorPhp\Console\Commands\CcdaParse;
use Illuminate\Support\ServiceProvider;

class CcdaParserProcessorProvider extends ServiceProvider
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
        $this->registerConfig();
        $this->commands([
            CcdaParse::class,
        ]);
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
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        /*$this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('customer.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'customer'
        );*/
    }
}
