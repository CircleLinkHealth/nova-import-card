<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;

class SetPatientConsented
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(PatientConsentedToService $event)
    {
        app(PatientServiceProcessorRepository::class)
            ->setPatientConsented(
                $event->getPatientId(),
                $event->getServiceCode(),
                Carbon::now()->startOfMonth()
            );
    }
}
