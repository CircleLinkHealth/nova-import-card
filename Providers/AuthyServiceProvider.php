<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Providers;

use Authy\AuthyApi;
use CircleLinkHealth\TwoFA\Contracts\AuthyApiable;
use CircleLinkHealth\TwoFA\Decorators\AuthyResponseLogger;
use Illuminate\Support\ServiceProvider;

class AuthyServiceProvider extends ServiceProvider
{
    protected $defer = true;
    
    public function provides()
    {
        return [
            AuthyApiable::class,
        ];
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
