<?php

namespace App\Observers;

use App\Patient;
use Carbon\Carbon;

class PatientObserver
{
    /**
     * Listen to the Patient created event.
     *
     * @param Patient $patient
     */
    public function created(Patient $patient)
    {
        if (!$patient->consent_date || !$patient->user) {
            return;
        }

        $patient->user->notes()->create([
            'author_id' => 948,
            'body' => "Patient consented on $patient->consent_date",
            'type' => 'Patient Consented',
            'performed_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

}