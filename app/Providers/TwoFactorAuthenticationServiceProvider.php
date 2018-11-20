<?php

namespace App\Providers;

use App\Contracts\TwoFactorAuthenticationApi;
use Authy\AuthyApi;
use Illuminate\Support\ServiceProvider;

class TwoFactorAuthenticationServiceProvider extends ServiceProvider
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
        $this->app->bind(TwoFactorAuthenticationApi::class, function() {
            return new AuthyApi(config('services.authy.api_key'));
        });
    }
}
