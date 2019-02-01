<?php


namespace App\Services;

use App\awvPatients;
use App\InvitationLink;
use Illuminate\Support\Facades\URL;

class SurveyInvitationLinksService
{
    public function checkPatientHasPastUrl($patient)
    {
        foreach ($patient->url as $oldUrl) {
            if ( ! $oldUrl->is_expired == true) {
                $oldUrl->update(['is_expired' => true]);
            }
        }
    }

    public function createAndSaveUrl($patient)
    {
        $surveyId = $this->tokenCreate();

        $url = URL::signedRoute('loginSurvey',
            [
                'patient'   => $patient->id,
                'survey_id' => $surveyId,
            ]);

        $urlToken = $this->parseUrl($url);

        InvitationLink::create([
            'awv_patient_id' => $patient->id,
            'survey_id'      => $surveyId,
            'link_token'     => $urlToken,
            'is_expired'     => false,
        ]);

        return $url;
    }


    /**
     * @return string
     */
    protected function tokenCreate(): string
    {
        do {
            $surveyId = rand();
        } while (InvitationLink::where('survey_id', $surveyId)->exists());

        return $surveyId;
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

    public function getPatientIdByPhoneNumber($phoneNumber)
    {
        $patientId = awvPatients::where('number', $phoneNumber)
                                ->firstOrFail();

        return $patientId;
    }
}