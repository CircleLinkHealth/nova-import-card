<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Providers;

use CircleLinkHealth\CcmBilling\Console\Commands\CheckPatientEndOfMonthCcmStatusLogsExist;
use CircleLinkHealth\CcmBilling\Console\Commands\CheckPatientSummariesHaveBeenCreated;
use CircleLinkHealth\CcmBilling\Console\Commands\GenerateEndOfMonthCcmStatusLogs;
use CircleLinkHealth\CcmBilling\Console\Commands\GenerateServiceSummariesForAllPracticeLocations;
use CircleLinkHealth\CcmBilling\Console\Commands\MigrateChargeableServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Console\Commands\MigratePracticeServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Console\Commands\ProcessAllPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Console\Commands\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Contracts\LocationProblemServiceRepository as LocationProblemServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository as PatientProcessorEloquentRepositoryInterface;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as PatientServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Repositories\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class CcmBillingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            MigratePracticeServicesFromChargeablesToLocationSummariesTable::class,
            MigrateChargeableServicesFromChargeablesToLocationSummariesTable::class,
            ProcessSinglePatientMonthlyServices::class,
            ProcessAllPracticePatientMonthlyServices::class,
            GenerateServiceSummariesForAllPracticeLocations::class,
            PatientMonthlyBillingProcessor::class,
            GenerateEndOfMonthCcmStatusLogs::class,
            PatientServiceProcessorRepository::class,
            CheckPatientEndOfMonthCcmStatusLogsExist::class,
            CheckPatientSummariesHaveBeenCreated::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerTranslations();
        $this->registerViews();

        if ($this->app->runningInConsole()) {
            $this->registerFactories();
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }

        //        $this->app->register(RouteServiceProvider::class);

        $this->app->bind(PatientMonthlyBillingProcessor::class, MonthlyProcessor::class);

        $this->app->singleton(PatientServiceRepositoryInterface::class, PatientServiceProcessorRepository::class);
        $this->app->singleton(LocationProblemServiceRepositoryInterface::class, LocationProblemServiceRepository::class);
        $this->app->singleton(LocationProcessorRepository::class, LocationProcessorEloquentRepository::class);
        $this->app->singleton(PatientProcessorEloquentRepositoryInterface::class, PatientProcessorEloquentRepository::class);

        $this->commands([
            MigratePracticeServicesFromChargeablesToLocationSummariesTable::class,
            MigrateChargeableServicesFromChargeablesToLocationSummariesTable::class,
            ProcessSinglePatientMonthlyServices::class,
            ProcessAllPracticePatientMonthlyServices::class,
            GenerateServiceSummariesForAllPracticeLocations::class,
            GenerateEndOfMonthCcmStatusLogs::class,
        ]);
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
