<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Helpers\PlaceholderEmailsVerifier;
use App\Survey;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\AppConfig\SelfEnrollmentLetterVersionSwitch;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;

class EnrolleesSurveyService
{
    /**
     * @return array
     */
    public function enrolleesQuestionsData(User $user)
    {
        $birthDate = $user->patientInfo->birth_date;
        if ( ! empty($birthDate)) {
            /** @var Carbon $birthDate */
            $birthDate = $birthDate->toDateString();
        }

        // It can be empty. Its ok.
        $primaryPhoneNumber = $user->getPhone();
        $isSurveyOnly       = $user->hasRole('survey-only');

        $letterLink = '';

        if ($isSurveyOnly) {
            $letterLink = url(config('services.selfEnrollment.url')."/review-letter/{$user->id}");
        }

        return [
            'dob'                    => $birthDate,
            'address'                => $user->address,
            'patientEmail'           => $this->patientEmail($user->email),
            'preferredContactNumber' => ! empty($primaryPhoneNumber) ? $primaryPhoneNumber : [],
            'isSurveyOnlyRole'       => $isSurveyOnly,
            'letterLink'             => $letterLink,
        ];
    }

    public function getSurveyData($patientId)
    {
        return SurveyService::getCurrentSurveyData($patientId, Survey::ENROLLEES);
    }

    public function getSurveyLogoPath(int $practiceId): ?string
    {
        if (SelfEnrollmentLetterVersionSwitch::loadNewVersionIfModelExists($practiceId)) {
            return EnrollmentInvitationLetterV2::getLetterLogoAndRememberV2($practiceId);
        }

        return EnrollmentInvitationLetter::getLetterLogoAndRememberV1($practiceId);
    }

    /**
     * @return string
     */
    private function patientEmail(string $email)
    {
        return ! PlaceholderEmailsVerifier::isClhGeneratedEmail($email) ? $email : '';
    }
}
