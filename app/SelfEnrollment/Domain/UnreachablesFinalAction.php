<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\Services\Enrollment\EnrollmentInvitationService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class UnreachablesFinalAction extends AbstractSelfEnrollableModelIterator
{
    public function action(User $user): void
    {
        $enrollmentInvitationService = app(EnrollmentInvitationService::class);

        if ( ! $user->isSurveyOnly()) {
//                    We need the enrolle model created when patient became "unreachable"......
//                    (see.PatientObserver & UnreachablePatientsToCaPanel)
//                   ...to set call_queue - doing this as temp. solution in order to be displayed on CA PANEL
            $enrollmentInvitationService->putIntoCallQueue($user->enrollee);

            return;
        }

        if ((bool) optional($user->enrollee->selfEnrollmentStatus)->logged_in) {
            $enrollmentInvitationService->putIntoCallQueue($user->enrollee);

            return;
        }

//                        Mark as non responsive means they will get a physical MAIL.
        $enrollmentInvitationService->markAsNonResponsive($user->enrollee);
        $enrollmentInvitationService->putIntoCallQueue($user->enrollee);
    }

    public function query(): Builder
    {
        return User::whereHas('notifications', function ($notification) {
            $notification->where([
                ['created_at', '<=', $this->end],
                ['created_at', '>=', $this->start],
            ])->selfEnrollmentInvites();
        })
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            })->has('enrollee')->with('enrollee');
    }
}
