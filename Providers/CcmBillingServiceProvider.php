<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Providers;

use CircleLinkHealth\CcmBilling\Contracts\LocationProblemServiceRepository as LocationProblemServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository as PatientProcessorEloquentRepositoryInterface;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as PatientServiceRepositoryInterface;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Repositories\CachedLocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\CachedPatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Repositories\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Auth;
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
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind(PatientMonthlyBillingProcessor::class, MonthlyProcessor::class);

        $this->app->singleton(PatientServiceRepositoryInterface::class, function ($app) {
            //todo: check global request object too?
            if (Auth::check()) {
                return $app->make(CachedPatientServiceProcessorRepository::class);
            }

            return $app->make(PatientServiceProcessorRepository::class);
        });

        $this->app->singleton(LocationProcessorRepository::class, function ($app) {
            if (Auth::check()) {
                return $app->make(CachedLocationProcessorEloquentRepository::class);
            }

            return $app->make(LocationProcessorEloquentRepository::class);
        });

        $this->app->singleton(LocationProblemServiceRepositoryInterface::class, LocationProblemServiceRepository::class);
        $this->app->singleton(PatientProcessorEloquentRepositoryInterface::class, PatientProcessorEloquentRepository::class);
    }
}
