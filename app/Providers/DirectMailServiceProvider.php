<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\DirectMail;
use App\Services\PhiMail\IncomingMessageHandler;
use App\Services\PhiMail\PhiMail;
use App\Services\PhiMail\PhiMailConnector;
use Illuminate\Support\ServiceProvider;

class DirectMailServiceProvider extends ServiceProvider
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
        $this->app->bind(
            DirectMail::class,
            function () {
                return new PhiMail(
                    app()->make(IncomingMessageHandler::class)
                );
            }
        );
    }
}
