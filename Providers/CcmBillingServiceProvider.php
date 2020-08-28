<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Providers;

use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as PatientRepositoryInterface;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository as PatientRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class CcmBillingServiceProvider extends ServiceProvider implements DeferrableProvider
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
//        $this->registerTranslations();
        $this->registerViews();
//        $this->registerConfig();

        if ($this->app->runningInConsole()) {
//        $this->registerFactories();
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }
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
        $this->app->register(RouteServiceProvider::class);

        $this->app->bind(PatientMonthlyBillingProcessor::class, new MonthlyProcessor());

        $this->app->singleton(PatientRepositoryInterface::class, new PatientRepository());
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        if ( ! isProductionEnv()) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/ccmbilling');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'ccmbilling');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'ccmbilling');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/ccmbilling');

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
                        return $path.'/modules/ccmbilling';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'ccmbilling'
        );
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('ccmbilling.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'ccmbilling'
        );
    }
}
