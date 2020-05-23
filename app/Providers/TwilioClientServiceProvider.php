<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\Services\TwilioClientable;
use App\Contracts\TwilioInterface;
use App\Services\CustomTwilioService;
use App\Services\TwilioClientService;
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
        $this->app->bind(TwilioClientable::class, function () {
            return new TwilioClientService();
        });

        $this->app->singleton(TwilioInterface::class, function () {
            return $this->app->make(CustomTwilioService::class);
        });
    }
}
