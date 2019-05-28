<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Authy\AuthyApi;
use CircleLinkHealth\TwoFA\Contracts\AuthyApiable;
use CircleLinkHealth\TwoFA\Decorators\AuthyResponseLogger;
use Illuminate\Support\ServiceProvider;

class AuthyServiceProvider extends ServiceProvider
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
        $this->app->bind(AuthyApiable::class, function () {
            return new AuthyResponseLogger(new AuthyApi(config('services.authy.api_key')));
        });
    }
}
