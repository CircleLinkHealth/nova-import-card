<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\CLH\CCD\Importer\ParsingStrategies\Helpers\UserMetaParserHelpers;
use Illuminate\Support\ServiceProvider;

class UserMetaParserHelpersServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    public function provides()
    {
        return ['userMetaParserHelpers'];
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
