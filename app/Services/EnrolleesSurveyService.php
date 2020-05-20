<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Helpers\PlaceholderEmailsVerifier;
use App\Survey;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;

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
            $letterLink = url(config('services.cpm.url')."/review-letter/{$user->id}");
        }

        return [
            'dob'                    => $birthDate,
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

    /**
     * @return string
     */
    private function patientEmail(string $email)
    {
        return ! PlaceholderEmailsVerifier::isClhGeneratedEmail($email) ? $email : '';
    }
}
