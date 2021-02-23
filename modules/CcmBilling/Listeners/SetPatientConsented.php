<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientBillingStatus;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientSummaries;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

class SetPatientConsented
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(PatientConsentedToService $event)
    {
        $repo =  app(PatientServiceProcessorRepository::class);

        $repo->setPatientConsented(
                $patientId = $event->getPatientId(),
                $event->getServiceCode(),
                $month = Carbon::now()->startOfMonth()
        );

        (app(ProcessPatientSummaries::class))->fromDTO(
            PatientMonthlyBillingDTO::generateFromUser(
                $repo->getPatientWithBillingDataForMonth($patientId, $month),
                $month
            )
        );
        (new ProcessPatientBillingStatus())->setPatientId($patientId)->setMonth($month)->execute();
    }
}
