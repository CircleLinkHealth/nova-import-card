<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Providers;

use CircleLinkHealth\CcmBilling\Caches\BillingCache;
use CircleLinkHealth\CcmBilling\Caches\BillingDataCache;
use CircleLinkHealth\CcmBilling\Console\CheckLocationSummariesHaveBeenCreatedCommand;
use CircleLinkHealth\CcmBilling\Console\CheckPatientEndOfMonthCcmStatusLogsExistForMonthCommand;
use CircleLinkHealth\CcmBilling\Console\CheckPatientSummariesHaveBeenCreatedCommand;
use CircleLinkHealth\CcmBilling\Console\CompareAbpV2vsV3;
use CircleLinkHealth\CcmBilling\Console\GenerateEndOfMonthCcmStatusLogsCommand;
use CircleLinkHealth\CcmBilling\Console\GenerateFakeDataForApproveBillablePatientsPage;
use CircleLinkHealth\CcmBilling\Console\GenerateServiceSummariesForAllPracticeLocationsCommand;
use CircleLinkHealth\CcmBilling\Console\ModifyPatientActivityAndReprocessTime;
use CircleLinkHealth\CcmBilling\Console\ProcessAllPracticePatientMonthlyServicesCommand;
use CircleLinkHealth\CcmBilling\Console\ResetPMSChargeableServicesForMonth;
use CircleLinkHealth\CcmBilling\Console\SeedChargeableServices;
use CircleLinkHealth\CcmBilling\Contracts\LocationProblemServiceRepository as LocationProblemServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as PatientServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Repositories\CachedLocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsService;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsServiceV3;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class CcmBillingDeferredServiceProvider extends ServiceProvider implements DeferrableProvider
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
            BillingCache::class,

            ApproveBillablePatientsService::class,
            ApproveBillablePatientsServiceV3::class,

            GenerateFakeDataForApproveBillablePatientsPage::class,
            CompareAbpV2vsV3::class,
            SeedChargeableServices::class,
            ResetPMSChargeableServicesForMonth::class,
            ModifyPatientActivityAndReprocessTime::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(PatientMonthlyBillingProcessor::class, MonthlyProcessor::class);
        $this->app->singleton(PatientServiceRepositoryInterface::class, PatientServiceProcessorRepository::class);
        $this->app->singleton(LocationProcessorRepository::class, CachedLocationProcessorEloquentRepository::class);
        $this->app->singleton(LocationProblemServiceRepositoryInterface::class, LocationProblemServiceRepository::class);
        $this->app->singleton(BillingCache::class, BillingDataCache::class);
        $this->app->singleton(ApproveBillablePatientsService::class);
        $this->app->singleton(ApproveBillablePatientsServiceV3::class);

        $this->commands([
            GenerateFakeDataForApproveBillablePatientsPage::class,
            CompareAbpV2vsV3::class,
            SeedChargeableServices::class,
            ResetPMSChargeableServicesForMonth::class,
            ModifyPatientActivityAndReprocessTime::class,

            CheckLocationSummariesHaveBeenCreatedCommand::class,
            GenerateServiceSummariesForAllPracticeLocationsCommand::class,
            GenerateEndOfMonthCcmStatusLogsCommand::class,
            ProcessAllPracticePatientMonthlyServicesCommand::class,
            CheckPatientSummariesHaveBeenCreatedCommand::class,
            CheckPatientEndOfMonthCcmStatusLogsExistForMonthCommand::class
        ]);
    }
}
