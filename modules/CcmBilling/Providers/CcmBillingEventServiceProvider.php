<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Providers;

use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;
use CircleLinkHealth\CcmBilling\Events\PatientProblemsChanged;
use CircleLinkHealth\CcmBilling\Events\PatientSuccessfulCallCreated;
use CircleLinkHealth\CcmBilling\Listeners\ProcessLocationPatientServices;
use CircleLinkHealth\CcmBilling\Listeners\ProcessLocationProblemServices;
use CircleLinkHealth\CcmBilling\Listeners\ProcessPatientServices;
use CircleLinkHealth\CcmBilling\Listeners\SetPatientConsented;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class CcmBillingEventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        LocationServicesAttached::class     => [
            ProcessLocationPatientServices::class,
            ProcessLocationProblemServices::class,
        ],
        PatientProblemsChanged::class       => [
            ProcessPatientServices::class,
        ],
        PatientActivityCreated::class       => [
            ProcessPatientServices::class,
        ],
        PatientSuccessfulCallCreated::class => [
            ProcessPatientServices::class,
        ],
        PatientConsentedToService::class    => [
            SetPatientConsented::class,
        ],
    ];
}