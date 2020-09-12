<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Providers;

use Illuminate\Support\ServiceProvider;

class EligibilityServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function boot()
    {
        $this->registerViews();

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/eligibility');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/eligibility';
        }, \Config::get('view.paths')), [$sourcePath]), 'eligibility');
    }
}
