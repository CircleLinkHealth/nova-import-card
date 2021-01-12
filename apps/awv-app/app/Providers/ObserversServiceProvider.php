<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Observers\SurveyInstanceObserver;
use App\SurveyInstance;
use Illuminate\Support\ServiceProvider;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        SurveyInstance::observe(SurveyInstanceObserver::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
