<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Customer\Events\CarePlanWasQAApproved;
use CircleLinkHealth\SharedModels\Observers\PatientObserver;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddPatientConsentNote implements ShouldQueue, ShouldBeEncrypted
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(CarePlanWasQAApproved $event)
    {
        if ($event->patient->notes->isNotEmpty()) {
            return;
        }

        (new PatientObserver())->sendPatientConsentedNote($event->patient->patientInfo);
    }
}
