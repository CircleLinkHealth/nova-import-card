<?php


namespace App\Services;

use App\awvPatients;
use App\InvitationLink;
use Illuminate\Support\Facades\URL;

class SurveyInvitationLinksService
{
    public function checkPatientHasPastUrl($patient)
    {//if patient has active url already -> set it to expired == true.
        foreach ($patient->url as $oldUrl) {
            if ( ! $oldUrl->is_expired == true) {
                $oldUrl->update(['is_expired' => true]);
            }
        }
    }

    public function createAndSaveUrl($patient)
    {
        $surveyId = $this->tokenCreate();
        //create a unique URL with patient id.
        $url = URL::temporarySignedRoute('loginSurvey', now()->addWeeks(2),
            [
                'patient'   => $patient->id,
                /*'survey_id' => $surveyId,*/ //@todo: if i pass survey_id to url, is different when i get it from the request
            ]);
        InvitationLink::create([
            'awv_patient_id' => $patient->id,
            'survey_id'      => $surveyId,
            'link_token'     => $url,
            'is_expired'     => false,
        ]);
        return $url;
    }

    /**
     * @return string
     */
    protected function tokenCreate(): string
    {
        do {//generate a random numeric string
            $surveyId = rand();
            //check if the token already exists and if it does, try again
        } while (InvitationLink::where('survey_id', $surveyId)->exists());

        return $surveyId;
    }

    public function getPatientIdByPhoneNumber($phoneNumber)
    {
        $patientId = awvPatients::with('url')
                              ->where('number', $phoneNumber)
                              ->firstOrFail();

        return $patientId;
    }
}