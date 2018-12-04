<?php

namespace App\Providers;

use App\Contracts\AuthyApiable;
use App\Decorators\AuthyResponseLogger;
use Authy\AuthyApi;
use Illuminate\Support\ServiceProvider;

class AuthyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(AuthyApiable::class, function () {
            return new AuthyResponseLogger(new AuthyApi(config('services.authy.api_key')));
        });
    }
}
