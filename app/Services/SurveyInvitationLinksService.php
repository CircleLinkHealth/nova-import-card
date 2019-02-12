<?php


namespace App\Services;

use App\InvitationLink;
use App\User;
use Illuminate\Support\Facades\URL;

class SurveyInvitationLinksService
{
    public function createAndSaveUrl($userId)
    {
        $this->expireAllPastUrls($userId);

        $surveyId = rand();
        $url      = URL::signedRoute('loginSurvey',
            [
                'patient'   => $userId,
                'survey_id' => $surveyId,
            ]);

        $urlToken = $this->parseUrl($url);

        InvitationLink::create([
            'awv_user_id'         => $userId,
            'survey_id'           => $surveyId,
            'link_token'          => $urlToken,
            'is_manually_expired' => false,
        ]);

        return $url;
    }

    public function getPatientPhoneNumberById($userId)
    {//im using User model cause eventually this method will accept user-names also.
        $patient = User::with([
            'patient' => function ($q) use ($userId) {
                $q->where('cpm_user_id', $userId);
            },
        ])
                       ->where('id', $userId)
                       ->firstOrFail();

        $phoneNumber = $patient->patient->number;

        return $phoneNumber;
    }

    public function expireAllPastUrls($userId)
    {
        InvitationLink::where('awv_user_id', $userId)
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
}