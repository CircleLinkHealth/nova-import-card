<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Providers;

use CircleLinkHealth\TwilioIntegration\Services\CustomTwilioService;
use CircleLinkHealth\TwilioIntegration\Services\TwilioClientable;
use CircleLinkHealth\TwilioIntegration\Services\TwilioClientService;
use CircleLinkHealth\TwilioIntegration\Services\TwilioInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TwilioClientServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    public function provides()
    {
        return [TwilioClientable::class, TwilioInterface::class];
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(TwilioClientable::class, function () {
            return new TwilioClientService();
        });

        $this->app->singleton(TwilioInterface::class, function () {
            return $this->app->make(CustomTwilioService::class);
        });
    }
}
