<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Providers;

use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerConfig();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerViews();
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/customer');

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
                        return $path.'/modules/customer';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'customer'
        );
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('customer.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'customer'
        );

        $this->publishes(
            [
                __DIR__.'/../Config/cerberus.php' => config_path('cerberus.php'),
            ],
            'cerberus'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/cerberus.php',
            'cerberus'
        );

        $this->publishes([
            __DIR__.'/../Config/medialibrary.php' => config_path('medialibrary.php'),
        ], 'medialibrary');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/medialibrary.php',
            'medialibrary'
        );
    }
}
