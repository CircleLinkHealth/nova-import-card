<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\Observers\PatientObserver;
use App\SelfEnrollment\AbstractSelfEnrollmentReminder;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\UnreachablePatientsToCaPanel;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class UnreachablesFinalAction extends AbstractSelfEnrollmentReminder
{
    /**
     * @var EnrollmentInvitationService
     */
    private $service;

    public function action(User $patient): void
    {
        //if enrollee has already been assigned to CA do not put back to call_queue
        //because this may result to a CA calling a declined patient again.
        if ( ! empty($patient->enrollee->care_ambassador_user_id)) {
            return;
        }

        if ($this->isUnreachablePatient($patient)) {
            return;
        }

        if ( ! $this->patientHasLoggedIn($patient)) {
            $this->service()->markAsNonResponsive($patient->enrollee);
        }

        $this->service()->putIntoCallQueue($patient->enrollee);
    }

    public function query(): Builder
    {
        return User::hasSelfEnrollmentInviteReminder($this->dateInviteSent)
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            })->has('enrollee')->with('enrollee');
    }

    /**
     * We need the enrollee model created when patient became "unreachable".
     *
     * @see PatientObserver
     * @see UnreachablePatientsToCaPanel
     */
    private function isUnreachablePatient(User $user): bool
    {
        if ( ! $user->isParticipant()) {
            return false;
        }
        if (Enrollee::QUEUE_AUTO_ENROLLMENT !== $user->enrollee->status) {
            return false;
        }
        if (Enrollee::UNREACHABLE_PATIENT !== $user->enrollee->source) {
            return false;
        }

        return true;
    }

    private function patientHasLoggedIn(User $patient): bool
    {
        return $patient->loginEvents()->exists();
    }

    private function service()
    {
        if (is_null($this->service)) {
            $this->service = app(EnrollmentInvitationService::class);
        }

        return $this->service;
    }
}
