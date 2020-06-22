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

class EnrollmentInvitationService
{
    /**
     * @param $practiceName
     * @param $practiceLetter
     * @param mixed $practiceNumber
     * @param $provider
     * @param bool $hideButtons
     *
     * @return array
     */
    public function createLetter($practiceName, EnrollmentInvitationLetter $practiceLetter, $practiceNumber, User $provider, $hideButtons = false)
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
            ? 'To enroll, click the "Get My Care Coach" button located both at the top and bottom of this letter.'
            : '';

        $optionalParagraph = ! $hideButtons
            ? "If you would like additional information, or are interested in enrolling today, please call <strong>$practiceNumber</strong>."
            : '';

        $buttonsSecondVersion = ! $hideButtons
        ? 'or click the "Get My Care Coach" button located both at the top and bottom of this letter.'
        : '';

        $optionalTitle = ! $hideButtons
            ? 'How do I sign up?'
            : '';

        // order has to be the same as the $varsToBeReplaced
        $practiceSigSrc = '';
        if ( ! empty($practiceLetter->customer_signature_src)) {
            if (ProviderSignature::SIGNATURE_VALUE === $practiceLetter->customer_signature_src) {
                $npiNumber      = $provider->load('providerInfo')->providerInfo->npi_number;
                $type           = ProviderSignature::SIGNATURE_PIC_TYPE;
                $practiceSigSrc = "<img src='/img/signatures/$practiceName/$npiNumber$type' alt='$practiceName' style='max-width: 100%;'/>";
            } else {
                $practiceSigSrc = "<img src='$practiceLetter->customer_signature_src'  alt='$practiceName' style='max-width: 100%;'/>";
            }
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

        $letter      = json_decode($practiceLetter->letter) ?? [];
        $letterPages = [];
        foreach ($letter as $page) {
            $body          = $page->body;
            $letterPages[] = str_replace($varsToBeReplaced, $replacementVars, $body);
        }

        return $letterPages;
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
