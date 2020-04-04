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
        $birthDate = '';
        if ($user->has('patientInfo')) {
            $birthDate = Carbon::parse($user->patientInfo->birth_date)->toDateString();
        }

        // It can be empty. Its ok.
        $primaryPhoneNumber = $user->phoneNumbers->where('is_primary', '=', true)->first()->number;
        $isSurveyOnly = $user->hasRole('survey-only');

        $letterLink = '';

        if ($isSurveyOnly) {
            $id = DB::table('enrollees')->where('user_id', $user->id)->select('id')->first()->id;

            $letter = DB::table('enrollables_invitation_links')
                ->where('invitationable_id', $id)
                ->select('url')
                ->first();

            $letterLink = $letter->url;
        }


        return [
            'dob' => $birthDate,
            'address' => $user->address,
            'patientEmail' => $user->email,
            'preferredContactNumber' => !empty($primaryPhoneNumber) ? $primaryPhoneNumber : [],
            'isSurveyOnlyRole' => $isSurveyOnly,
            'letterLink' => $letterLink
        ];
    }
}
