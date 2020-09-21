<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Providers;

use CircleLinkHealth\CpmAdmin\Console\Commands\CountBillablePatientsForMonth;
use CircleLinkHealth\CpmAdmin\Console\Commands\CountPatientMonthlySummaryCalls;
use CircleLinkHealth\CpmAdmin\Console\Commands\SyncNumberOfCallsForCurrentMonth;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class CpmAdminServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->registerVueComponents();
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
        $this->registerTranslations();
        $this->registerViews();
        if (class_exists(\App\User::class)) {
            Relation::morphMap([
                \CircleLinkHealth\Customer\Entities\User::class => \App\User::class,
            ]);
        }
        $this->commands([
            CountBillablePatientsForMonth::class,
            CountPatientMonthlySummaryCalls::class,
            SyncNumberOfCallsForCurrentMonth::class,
        ]);
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        app(Factory::class)->load(__DIR__.'/../Database/Factories');
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/cpm-admin');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'cpm-admin');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'cpm-admin');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/cpm-admin');

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
                        return $path.'/modules/cpm-admin';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'cpm-admin'
        );
    }

    public function registerVueComponents()
    {
        $this->publishes([
            __DIR__.'/../Resources/assets/js/' => resource_path(
                'assets/js/'
            ), ], 'vue-components');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('cpm-admin.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'cpm-admin'
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
    }
}
