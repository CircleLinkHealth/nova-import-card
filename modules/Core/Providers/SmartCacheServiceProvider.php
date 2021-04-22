<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use CircleLinkHealth\Core\SmartCacheManager;
use Illuminate\Cache\CacheServiceProvider;

class SmartCacheServiceProvider extends CacheServiceProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return parent::provides();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton('cache', function ($app) {
            return new SmartCacheManager($app);
        });
    }
}
