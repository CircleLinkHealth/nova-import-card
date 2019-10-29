<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use App\Contracts\Services\TwilioClientable;
use App\Services\TwilioClientService;
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
        return [TwilioClientable::class];
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(TwilioClientable::class, function () {
            return new TwilioClientService();
        });
    }
}
