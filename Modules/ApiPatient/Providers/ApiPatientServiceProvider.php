<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class ApiPatientServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     */
    public function boot()
    {
//        commented out so we can use deferred loading for this provider
//        currently the package does not have any migrations, views, translations, factories, blah
//        uncomment if we add any of the above

//        $this->registerTranslations();
//        $this->registerViews();

//        if ($this->app->runningInConsole()) {
//        $this->registerConfig();
//        $this->registerFactories();
//        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
//        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        if ( ! app()->environment('production')) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/apipatient');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'apipatient');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'apipatient');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/apipatient');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/apipatient';
        }, \Config::get('view.paths')), [$sourcePath]), 'apipatient');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('apipatient.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'apipatient'
        );
    }
}
