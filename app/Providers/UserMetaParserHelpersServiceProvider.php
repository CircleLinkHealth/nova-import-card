<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\CLH\CCD\Importer\ParsingStrategies\Helpers\UserMetaParserHelpers;
use Illuminate\Support\ServiceProvider;

class UserMetaParserHelpersServiceProvider extends ServiceProvider
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
        $this->app->bind('userMetaParserHelpers', function () {
            return new UserMetaParserHelpers();
        });
    }
}
