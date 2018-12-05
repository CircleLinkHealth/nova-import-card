<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\CLH\Helpers\StringManipulation;
use Illuminate\Support\ServiceProvider;

class StringManipulationServiceProvider extends ServiceProvider
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
        $this->app->bind('stringManipulation', function () {
            return new StringManipulation();
        });
    }
}
