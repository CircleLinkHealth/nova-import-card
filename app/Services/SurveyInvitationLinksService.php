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
        //create a unique URL with patient id.
        $url = URL::temporarySignedRoute('loginSurvey', now()->addWeeks(2),
            ['patient' => $patient->id]);
        //save URL to DB
        InvitationLink::create([
            'awv_patient_id' => $patient->id,
            //todo:ask how should i treat survey_id. Autoincrement??
            'survey_id'     => '8',
            'link_token'    => $url,
            'is_expired'    => false,
        ]);

        return $url;
    }

    public function getPatientIdByPhoneNumber($phoneNumber)
    {
        $patient = awvPatients::with('url')
                              ->where('number', $phoneNumber)
                              ->firstOrFail();

        return $patient;
    }
}