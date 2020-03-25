<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;

class EnrollmentInvitationService
{
    use EnrollableManagement;

    /**
     * @param $practiceName
     * @param $practiceLetter
     * @param $careAmbassadorPhoneNumber
     * @param $provider
     * @param bool $hasButtons
     *
     * @return array
     */
    public function createLetter($practiceName, $practiceLetter, $careAmbassadorPhoneNumber, $provider, $hasButtons = true)
    {
        $varsToBeReplaced = [
            EnrollmentInvitationLetter::PROVIDER_LAST_NAME,
            EnrollmentInvitationLetter::CARE_AMBASSADOR_NUMBER,
            EnrollmentInvitationLetter::SIGNATORY_NAME,
            EnrollmentInvitationLetter::PRACTICE_NAME,
            EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON,
            EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC,
        ];

        $buttonsLocation = $hasButtons
            ? 'To enroll, click the enroll button at the top/bottom of this letter.'
            : '';

        $replacementVars = [
            $provider->last_name,
            $careAmbassadorPhoneNumber,
            $provider->display_name,
            $practiceName,
            $buttonsLocation,
            '[CUSTOMER_SIGNATURE_PIC]',
        ];

        $letter      = json_decode($practiceLetter->letter);
        $letterPages = [];
        foreach ($letter as $page) {
            $body          = $page->body;
            $letterPages[] = str_replace($varsToBeReplaced, $replacementVars, $body);
        }

        return $letterPages;
    }

    /**
     * @param Enrollee $enrollee
     */
    public function putIntoCallQueue(Enrollee $enrollee)
    {
        $enrollee->update(
            [
                'status' => 'call_queue',
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
            $surveyLink = $this->getSurveyInvitationLink($enrollable->patientInfo->id);
        } catch (\Exception $exception) {
            \Log::alert($exception);
        }

        return redirect($surveyLink->url);
    }

    /**
     * @param Enrollee $enrollee
     */
    public function setEnrollmentCallOnDelivery(Enrollee $enrollee)
    {
        $enrollee->update(
            [
                'status'             => 'call_queue',
                'requested_callback' => Carbon::parse(now())->toDate(),
            ]
        );
    }

    /**
     * @param Enrollee $enrollee
     */
    public function markAsNonResponsive(Enrollee $enrollee)
    {
        $enrollee->update([
            'enrollment_non_responsive' => true
        ]);

    }
}