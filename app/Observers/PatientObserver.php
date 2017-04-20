<?php

namespace App\Observers;

use App\Patient;

class PatientObserver
{
    /**
     * Listen to the Patient created event.
     *
     * @param Patient $patient
     */
    public function created(Patient $patient)
    {
        $patient->user->notes()->create([
            'body' => "Patient consented on $patient->consent_date",
            'type' => 'Note',
        ]);
    }

}