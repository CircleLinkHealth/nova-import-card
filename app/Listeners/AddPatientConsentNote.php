<?php

namespace App\Listeners;

use App\Events\CarePlanWasQAApproved;
use App\Observers\PatientObserver;

class AddPatientConsentNote
{
    /**
     * Handle the event.
     *
     * @param  object  $event
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
