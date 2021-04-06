<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Providers;

use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SmartOnFhirSsoDeferrableServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            SsoService::class,
        ];
    }

    public function register()
    {
        $this->app->singleton(SsoService::class, function ($app) {
            return new SsoService();
        });
    }
}
