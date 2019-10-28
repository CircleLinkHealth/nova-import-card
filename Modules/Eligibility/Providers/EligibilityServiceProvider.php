<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Providers;

use App\Services\AthenaAPI\Calls;
use App\Services\AthenaAPI\Connection;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiConnection;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class EligibilityServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'athena.api',
            AthenaApiImplementation::class,
            AthenaApiConnection::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(AthenaApiImplementation::class, function () {
            return new Calls();
        });

        $this->app->singleton(AthenaApiConnection::class, function () {
            $key = config('services.athena.key');
            $secret = config('services.athena.secret');
            $version = config('services.athena.version');

            return new Connection($version, $key, $secret);
        });
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
        $langPath = resource_path('lang/modules/eligibility');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'eligibility');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'eligibility');
        }
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

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('eligibility.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'eligibility'
        );
    }
}
