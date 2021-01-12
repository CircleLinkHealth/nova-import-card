<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AppDeferredServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            DevelopmentServiceProvider::class,
        ];
    }

    /**
     * Register services.
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            DevelopmentServiceProvider::class;
        }
    }
}
