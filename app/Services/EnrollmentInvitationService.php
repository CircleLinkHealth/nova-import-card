<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\ProviderSignature;
use App\SelfEnrollment\Helpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Database\Eloquent\Model;

class EnrollmentInvitationService
{
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
    public function sendToAwv($enrollable)
    {
        try {
            $surveyLink = Helpers::getSurveyInvitationLink($enrollable->patientInfo);
        } catch (\Exception $exception) {
            \Log::alert($exception);
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
