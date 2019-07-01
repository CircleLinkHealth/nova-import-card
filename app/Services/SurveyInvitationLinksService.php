<?php


namespace App\Services;

use App\InvitationLink;
use App\Patient;
use App\Survey;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class SurveyInvitationLinksService
{
    const HRA = 'HRA';

    public function createAndSaveUrl($userId)
    {
        $patient = Patient::where('user_id', $userId)
                          ->select('id')
                          ->firstOrFail();

        $patientInfoId = $patient->id;

        $this->expireAllPastUrls($patientInfoId);

        $survey = Survey::where('name', $this::HRA)
                        ->select('id')
                        ->firstOrFail();

        $surveyId = $survey->id;

        $url = URL::signedRoute('loginSurvey',
            [
                'user_id'   => $userId,
                'survey_id' => $surveyId,
                'timestamp' => Carbon::now()->timestamp
            ]);

        $urlToken = $this->parseUrl($url);

        InvitationLink::create([
            'patient_info_id'     => $patientInfoId,
            'survey_id'           => $surveyId,
            'link_token'          => $urlToken,
            'is_manually_expired' => false,
        ]);

        return $url;
    }

    public function expireAllPastUrls($patientInfoId)
    {
        InvitationLink::where('patient_info_id', $patientInfoId)
                      ->where('is_manually_expired', '=', 0)
                      ->update(['is_manually_expired' => true]);
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function parseUrl($url)
    {
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $output);

        $urlToken = $output['signature'];

        return $urlToken;
    }

    public function getPatientPhoneNumberById($userId)
    {//im using User model cause eventually this method will accept user-names also.
        $user = User::with([
            'phoneNumber' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            },
        ])
                    ->where('id', $userId)
                    ->firstOrFail();

        $phoneNumber = $user->phoneNumber->number;

        return $phoneNumber;
    }
}
