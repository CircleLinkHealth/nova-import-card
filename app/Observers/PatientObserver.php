<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Enrollee;
use App\TargetPatient;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;

class PatientObserver
{
    public function attachTargetPatient(Patient $patient)
    {
        $user = $patient->user;

        if ($user) {
            $enrollee = Enrollee::where([
                ['mrn', '=', $patient->mrn_number],
                ['practice_id', '=', optional($user->primaryPractice)->id],
            ])->first();

            if ($enrollee) {
                //find target patient with matching ehr_patient_id, update or create TargetPatient
                $targetPatient = TargetPatient::where('enrollee_id', $enrollee->id)
                    ->orWhere('ehr_patient_id', $enrollee->mrn)
                    ->first();

                if ($targetPatient) {
                    $user->ehrInfo()->save($targetPatient);
                }
            }
        }
    }

    /**
     * Listen to the Patient created event.
     */
    public function created(Patient $patient)
    {
        if ($patient->consent_date) {
            $this->sendPatientConsentedNote($patient);
        }
    }

    public function saving(Patient $patient)
    {
        if ($patient->isDirty('mrn_number')) {
            $this->attachTargetPatient($patient);
        }
    }

    public function sendPatientConsentedNote(Patient $patient)
    {
        if ( ! optional($patient->user->careplan)->isProviderApproved() || ! auth()->check()) {
            return;
        }

        $note = $patient->user->notes()->create([
            'author_id'    => 948,
            'body'         => "Patient consented on {$patient->consent_date}",
            'type'         => 'Patient Consented',
            'performed_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * Listen to the Patient updated event.
     */
    public function updated(Patient $patient)
    {
        if ($patient->isDirty('consent_date')) {
            $this->sendPatientConsentedNote($patient);
        }
    }

    /**
     * Listen to the Patient updated event.
     */
    public function updating(Patient $patient)
    {
        if ($patient->isDirty('date_paused')) {
            $patient->paused_letter_printed_at = null;
        }

        if ($patient->isDirty('ccm_status')) {
            if (Patient::UNREACHABLE == $patient->getOriginal('ccm_status') && Patient::ENROLLED == $patient->ccm_status) {
                $patient->no_call_attempts_since_last_success = 0;
            }
        }
    }
}
