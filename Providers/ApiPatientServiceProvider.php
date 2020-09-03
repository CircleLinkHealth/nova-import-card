<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class ApiPatientServiceProvider extends ServiceProvider implements DeferrableProvider
{
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
        $this->app->register(RouteServiceProvider::class);
    }
}
