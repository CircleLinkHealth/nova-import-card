<?php


namespace CircleLinkHealth\SelfEnrollment\Services;

use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\ValueObjects\PracticeLetterData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SelfEnrollmentLetterService
{
    /**
     * @param User $patient
     * @param EnrollmentInvitationLetterV2 $letter
     * @param string $dateLetterSent
     * @return PracticeLetterData
     */
    public function createLetterToRender(User $patient, EnrollmentInvitationLetterV2 $letter, string $dateLetterSent): PracticeLetterData
    {
        $logoUrl = $this->getPracticeLogoUrl($letter);
        $signaturesForLetter = app(LetterSignaturesConstructor::class)->getSignaturesForCurrentLetter($letter, $patient);
        $letterBody = $this->replaceLetterVariables($letter->body, $patient, $dateLetterSent);

        return new PracticeLetterData($letterBody, $letter->options, $logoUrl, $signaturesForLetter);
    }


    /**
     * @param EnrollmentInvitationLetterV2 $letter
     * @return string
     */
    public function getPracticeLogoUrl(EnrollmentInvitationLetterV2 $letter): string
    {
       $logoUrl = EnrollmentInvitationLetterV2::getLetterLogoAndRememberV2($letter->practice_id, $letter);

        if (! $logoUrl){
            $message = "Could not find Logo in media table for self enrollment letter with id:[$letter->id].";
            Log::error($message);
            sendSlackMessage('#self_enrollment_logs', $message);
            return '';
        }

        return $logoUrl;
    }

    /**
     * @param string $letterBody
     * @param User $patient
     * @param Collection|null $signatures
     * @return string
     */
    private function replaceLetterVariables(string $letterBody, User $patient, string $dateLetterSent): string
    {
        $variablesToBeReplaced = [
            EnrollmentInvitationLetterV2::PATIENT_FIRST_NAME,
            EnrollmentInvitationLetterV2::PATIENT_LAST_NAME,
            EnrollmentInvitationLetterV2::DATE_LETTER_SENT,
        ];

        $replacementVariables =  [
            $patient->first_name,
            $patient->last_name,
            $dateLetterSent

        ];

        return str_replace($variablesToBeReplaced, $replacementVariables, $letterBody);

    }
}