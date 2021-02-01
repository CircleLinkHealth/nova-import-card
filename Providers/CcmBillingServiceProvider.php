<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Providers;

use CircleLinkHealth\CcmBilling\Caches\BillingCache;
use CircleLinkHealth\CcmBilling\Caches\BillingDataCache;
use CircleLinkHealth\CcmBilling\Console\ModifyPatientActivityAndReprocessTime;
use CircleLinkHealth\CcmBilling\Console\ResetPMSChargeableServicesForMonth;
use CircleLinkHealth\CcmBilling\Console\SeedChargeableServices;
use CircleLinkHealth\CcmBilling\Contracts\LocationProblemServiceRepository as LocationProblemServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository as PatientProcessorEloquentRepositoryInterface;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as PatientServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Repositories\CachedLocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\CachedPatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Repositories\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientProcessorEloquentRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
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
            PatientMonthlyBillingProcessor::class,
            PatientServiceRepositoryInterface::class,
            LocationProcessorRepository::class,
            LocationProblemServiceRepositoryInterface::class,
            PatientProcessorEloquentRepositoryInterface::class,
            BillingCache::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->commands([
            SeedChargeableServices::class,
            ResetPMSChargeableServicesForMonth::class,
            ModifyPatientActivityAndReprocessTime::class,
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->singleton(PatientMonthlyBillingProcessor::class, MonthlyProcessor::class);
        $this->app->singleton(PatientServiceRepositoryInterface::class, CachedPatientServiceProcessorRepository::class);
        $this->app->singleton(LocationProcessorRepository::class, CachedLocationProcessorEloquentRepository::class);
        $this->app->singleton(LocationProblemServiceRepositoryInterface::class, LocationProblemServiceRepository::class);
        $this->app->singleton(PatientProcessorEloquentRepositoryInterface::class, PatientProcessorEloquentRepository::class);
        $this->app->singleton(BillingCache::class, BillingDataCache::class);
    }
}
