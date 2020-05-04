<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Services\TwilioClientService;
use Illuminate\Support\ServiceProvider;

class TwilioClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(TwilioClientService::class, function () {
            return new TwilioClientService();
        });
    }
}
