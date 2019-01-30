<?php

namespace App\Http\Controllers;


use App\awvPatients;
use App\InvitationLink;
use Illuminate\Support\Facades\URL;

class InvitationLinksController extends Controller
{
    public function createSendUrl($number)
    {
        $patient = awvPatients::with('url')
                              ->where('number', $number)
                              ->firstOrFail();

        foreach ($patient->url as $oldUrl) {
            if ( ! $oldUrl->is_expired == true) {
                $oldUrl->update(['is_expired' => true]);
            }
        }
        //create a unique URL with patient id.
        $url = URL::temporarySignedRoute('loginSurvey', now()->addWeeks(2),
            ['patient' => $patient->id]);

        InvitationLink::create([
            'aw_patient_id' => $patient->id,
            //michalis how should i handle the survey_id? autoincrement?
            'survey_id'     => '8',
            'link_token'    => $url,
            'is_expired'    => false,
        ]);
        //todo:HERE - send SMS using Twilio with $url and then return feedback
        return 'Invitation has been send';
    }

}
