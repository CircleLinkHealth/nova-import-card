<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Observers;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Jobs\RemoveScheduledCallsForUnenrolledPatients;
use CircleLinkHealth\Customer\Listeners\AssignPatientToStandByNurse;
use CircleLinkHealth\Customer\Traits\UnreachablePatientsToCaPanel;
use CircleLinkHealth\SharedModels\Notifications\PatientUnsuccessfulCallNotification;
use CircleLinkHealth\SharedModels\Services\SchedulerService;

class PatientObserver
{
    use UnreachablePatientsToCaPanel;

    /**
     * Listen to the Patient created event.
     */
    public function created(Patient $patient)
    {
        if ($patient->consent_date) {
            $this->sendPatientConsentedNote($patient);
        }
    }

    public function saved(Patient $patient)
    {
        $this->processPatientSummariesIfYouShould($patient);

        if ($patient->isDirty('ccm_status')) {
            if (Patient::UNREACHABLE === $patient->ccm_status
                && ! $patient->user->hasRole('survey-only')) {
                $this->createEnrolleModelForPatient($patient->user);
            }

            if (in_array(
                $patient->ccm_status,
                [
                    Patient::WITHDRAWN_1ST_CALL,
                    Patient::WITHDRAWN,
                    Patient::PAUSED,
                    Patient::UNREACHABLE,
                ]
            )) {
                RemoveScheduledCallsForUnenrolledPatients::dispatch([$patient->user_id]);
            }

            $this->assignToStandByNurseIfChangedToEnrolled($patient);
        }

        if ($patient->isDirty('no_call_attempts_since_last_success') && $patient->no_call_attempts_since_last_success > 0) {
            // ROAD-98 - sms patients with unsuccessful call
            $this->sendUnsuccessfulCallNotificationToPatient($patient);
        }
    }

    public function saving(Patient $patient)
    {
        if ($this->statusChangedToEnrolled($patient)) {
            $patient->no_call_attempts_since_last_success = 0;
        }
    }

    public function sendPatientConsentedNote(Patient $patient)
    {
        if ( ! optional($patient->user->careplan)->isProviderApproved() || ! auth()->check()) {
            return;
        }

        $note = $patient->user->notes()->create(
            [
                'author_id'    => PatientSupportUser::id(),
                'body'         => "Patient consented on {$patient->consent_date}",
                'type'         => 'Patient Consented',
                'performed_at' => Carbon::now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Listen to the Patient updated event.
     */
    public function updated(Patient $patient)
    {
        if ($patient->isDirty('consent_date')) {
            $this->sendPatientConsentedNote($patient);
        }

        $this->assignToStandByNurseIfChangedToEnrolled($patient);
    }

    /**
     * Listen to the Patient updated event.
     * Reset paused_letter_printed_at in case date_paused was changed.
     * Make sure patient has scheduled call in case status was changed AND is now 'enrolled'.
     * Make sure call attempts counter is reset in case status was 'unreachable' and is now 'enrolled'.
     */
    public function updating(Patient $patient)
    {
        if ($patient->isDirty('date_paused')) {
            $patient->paused_letter_printed_at = null;
        }

        if ($patient->isDirty('ccm_status')) {
            if ($this->statusChangedToEnrolled($patient)) {
                $patient->no_call_attempts_since_last_success = 0;
            }
        }
    }

    private function assignToStandByNurseIfChangedToEnrolled(Patient $patient)
    {
        if ( ! $patient->user->isParticipant()) {
            return;
        }
        if ($patient->isDirty('ccm_status')) {
            if ($this->statusChangedToEnrolled($patient)) {
                $patient->loadMissing('user');
                AssignPatientToStandByNurse::assign($patient->user);
            }
        }
    }

    private function locationChanged(Patient $patient): bool
    {
        $oldValue = $patient->getOriginal('preferred_contact_location');
        $newValue = $patient->preferred_contact_location;

        if (is_null($newValue)) {
            sendSlackMessage('#billing_alerts', "Warning! (PatientObserver:) Patient ({$patient->user_id}) does not have a preferred contact location.");

            return false;
        }

        if ($oldValue == $newValue) {
            return false;
        }

        return true;
    }

    private function processPatientSummariesIfYouShould(Patient $patient): void
    {
        if (is_null($patient->preferred_contact_location)) {
            return;
        }

        if ( ! $patient->user->isParticipant()){
            return;
        }

        if ($this->locationChanged($patient) || $this->statusChangedToOrFromEnrolled($patient)) {
            ProcessSinglePatientMonthlyServices::dispatch($patient->user_id, Carbon::now()->startOfMonth());
        }
    }

    private function sendUnsuccessfulCallNotificationToPatient(Patient $patient)
    {
        if ( ! isUnsuccessfulCallPatientNotificationEnabled()) {
            return;
        }

        if (Patient::ENROLLED !== $patient->ccm_status) {
            return;
        }

        //send notification only on first unsuccessful attempt, and then every after 2, so 1st, 3rd, 5th
        if (0 === $patient->no_call_attempts_since_last_success % 2) {
            return;
        }

        $call = SchedulerService::getLastUnsuccessfulCall($patient->user_id, now(), true);
        if ( ! $call) {
            return;
        }

        $call->inboundUser->notify(new PatientUnsuccessfulCallNotification($call, false));
    }

    private function statusChangedToOrFromEnrolled(Patient $patient): bool
    {
        $oldValue = $patient->getOriginal('ccm_status');
        $newValue = $patient->ccm_status;

        if (! in_array(Patient::ENROLLED, [
            $oldValue,
            $newValue
        ])){
            return false;
        }

        return true;
    }
}
