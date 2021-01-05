<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Services;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UnreachablePatientsToCaPanel;
use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Facades\Log;

class EnrollmentInvitationService
{
    /**
     * We need the enrollee model created when patient became "unreachable".
     *
     * @see PatientObserver
     * @see UnreachablePatientsToCaPanel
     */
    public function isUnreachablePatient(User $user): bool
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

    /**
     * Non responsive patients were not reachable during the SelfEnrollment process.
     * Marking them as unreachable means they will get a physical letter inviting them to enroll mailed to their address.
     */
    public function markAsNonResponsive(Enrollee $enrollee)
    {
        $enrollee->update([
            'enrollment_non_responsive' => true,
            'auto_enrollment_triggered' => true,
        ]);
    }

    public function putIntoCallQueue(Enrollee $enrollee, Carbon $earliestDayToCall)
    {
        $enrollee->update(
            [
                'status'                    => Enrollee::TO_CALL,
                'auto_enrollment_triggered' => true,
                'requested_callback'        => $earliestDayToCall,
            ]
        );
    }

    /**
     * @param $enrollable
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendToAwv(User $enrollable)
    {
        try {
            $surveyLink = Helpers::getSurveyInvitationLink($enrollable);
        } catch (\Exception $exception) {
            Log::alert($exception);
            throw new \Exception($exception);
        }

        return redirect($surveyLink->url);
    }

    public function setEnrollmentCallOnDelivery(Enrollee $enrollee)
    {
        $enrollee->update(
            [
                'status'                    => 'call_queue',
                'requested_callback'        => Carbon::now(),
                'auto_enrollment_triggered' => true,
            ]
        );
    }
}
