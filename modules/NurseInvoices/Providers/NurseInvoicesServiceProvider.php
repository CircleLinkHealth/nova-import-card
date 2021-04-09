<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Providers;

use Illuminate\Support\ServiceProvider;

class NurseInvoicesServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/nurseinvoices');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes(
            [
                $sourcePath => $viewPath,
            ],
            'views'
        );

        $this->loadViewsFrom(
            array_merge(
                array_map(
                    function ($path) {
                        return $path.'/modules/nurseinvoices';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'nurseinvoices'
        );
    }
}
