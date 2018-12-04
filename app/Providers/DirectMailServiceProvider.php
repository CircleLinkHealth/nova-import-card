<?php

namespace App\Providers;

use App\Contracts\DirectMail;
use App\Services\PhiMail\PhiMail;
use Illuminate\Support\ServiceProvider;

class DirectMailServiceProvider extends ServiceProvider
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
        $this->app->bind(DirectMail::class, function () {
            return new PhiMail();
        });
    }
}
