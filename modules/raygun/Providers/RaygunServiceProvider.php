<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Raygun\Providers;

use Illuminate\Support\ServiceProvider;

class RaygunServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerViews();
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/raygun');

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
                        return $path.'/modules/raygun';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'cpm-module-raygun'
        );
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('cpm-module-raygun.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'cpm-module-raygun'
        );
    }
}
