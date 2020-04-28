<?php

namespace App\Services;

use App\Survey;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\DB;

class EnrolleesSurveyService
{
    public function getSurveyData($patientId)
    {
        return SurveyService::getCurrentSurveyData($patientId, Survey::ENROLLEES);
    }

    /**
     * @param User $user
     * @return array
     */
    public function enrolleesQuestionsData(User $user)
    {
        $birthDate = $user->patientInfo->birth_date;
        if (! empty($birthDate)) {
            /** @var Carbon $birthDate */
            $birthDate = $birthDate->toDateString();
        }

        // It can be empty. Its ok.
        $primaryPhoneNumber = $user->getPhone();
        $isSurveyOnly = $user->hasRole('survey-only');

        $letterLink = '';

        if ($isSurveyOnly) {
            $letterLink = url(config('services.cpm.url')."/review-letter/{$user->id}");
        }

        return [
            'dob' => $birthDate,
            'address' => $user->address,
            'patientEmail' => $user->email,
            'preferredContactNumber' => ! empty($primaryPhoneNumber) ? $primaryPhoneNumber : [],
            'isSurveyOnlyRole' => $isSurveyOnly,
            'letterLink' => $letterLink,
        ];
    }
}
