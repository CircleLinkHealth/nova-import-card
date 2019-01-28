<?php

namespace App\Http\Controllers;

use App\awvPatients;
use App\InvitationLink;
use App\User;
use Illuminate\Support\Facades\URL;


class AwvPatientsController extends Controller
{
    public function enterPhoneNumber()
    {
        $number  = '543-722-5771';
        $patient = awvPatients::where('number', $number)->first();

        return redirect(route('create-url', [$patient]));
    }

    public function createUrl($patient)
    {
        $url = URL::temporarySignedRoute('login-survey', now()->addWeeks(2), ['patient' => $patient]);
        InvitationLink::create([
            'aw_patient_id' => $patient,
            'survey_id'     => '5',
            'link_token'    => $url,
            'is_expired'    => false,
        ]);

        //send sms here with $url
        return 'invitation send';
    }

    public function authenticateInvitedUser($patient)
    {
        //todo: if link has expired click and give option to create another one.
        // Check if link has expired else expired the previous one an then create new//https://dev.to/fwartner/laravel-56---user-activation-with-signed-routes--notifications-oaa
      //else{
        $patientId = InvitationLink::where('aw_patient_id', $patient)->get();
        foreach ($patientId as $patient) {
            $patient->update(['is_expired' => true]);
        }
        return 'Send User to Survey here';
    }

    public function authSurveyLogin()
    {
        $name ='Lauren Breitenberg';
        $birthDate= '1991-06-06';

        if ( ! User::where('name', $name)->first()) {
            return 'Name does not exists in our DB';
        }
        if(! awvPatients::where('birth_date', $birthDate)->first()){
            return 'Date Of Birth is Wrong';
        }

        return 'Login to survey';
    }
}
