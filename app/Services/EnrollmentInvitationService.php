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
     * @param mixed $practiceNumber
     * @param $provider
     * @param bool $hideButtons
     *
     * @return array
     */
    public function createLetter($practiceName, EnrollmentInvitationLetter $practiceLetter, $practiceNumber, $provider, $hideButtons = false)
    {
        $varsToBeReplaced = [
            EnrollmentInvitationLetter::PROVIDER_LAST_NAME,
            EnrollmentInvitationLetter::PRACTICE_NUMBER,
            EnrollmentInvitationLetter::SIGNATORY_NAME,
            EnrollmentInvitationLetter::PRACTICE_NAME,
            EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON,
            EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC,
            EnrollmentInvitationLetter::OPTIONAL_PARAGRAPH,
            EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON_SECOND,
            EnrollmentInvitationLetter::OPTIONAL_TITLE,
        ];

        $buttonsLocation = ! $hideButtons
            ? 'To enroll, click the "Get My Care Coach" button at the top/bottom of this letter.'
            : '';

        $optionalParagraph = ! $hideButtons
            ? "If you would like additional information, or are interested in enrolling today, please call <strong>$practiceNumber</strong>."
            : '';

        $buttonsSecondVersion = ! $hideButtons
        ? 'or click the "Get My Care Coach" button above.'
        : '';

        $optionalTitle = ! $hideButtons
            ? 'How do I sign up?'
            : '';

        // order has to be the same as the $varsToBeReplaced
        $practiceSigSrc = '';
        if ( ! empty($practiceLetter->customer_signature_src)) {
            $practiceSigSrc = "<img src='$practiceLetter->customer_signature_src'  alt='$practiceName' style='max-width: 100%;'/>";
        }
        $replacementVars = [
            $provider->last_name,
            $practiceNumber,
            $provider->display_name,
            $practiceName,
            $buttonsLocation,
            $practiceSigSrc,
            $optionalParagraph,
            $buttonsSecondVersion,
            $optionalTitle,
        ];

        $letter      = json_decode($practiceLetter->letter);
        $letterPages = [];
        foreach ($letter as $page) {
            $body          = $page->body;
            $letterPages[] = str_replace($varsToBeReplaced, $replacementVars, $body);
        }

        return $letterPages;
    }

    public function markAsNonResponsive(Enrollee $enrollee)
    {
        $enrollee->update([
            'enrollment_non_responsive' => true,
            'auto_enrollment_triggered' => true,
        ]);
    }

    public function putIntoCallQueue(Enrollee $enrollee)
    {
        $enrollee->update(
            [
                'status'                    => Enrollee::TO_CALL,
                'auto_enrollment_triggered' => true,
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
