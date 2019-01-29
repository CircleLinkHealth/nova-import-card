<?php

namespace App\Http\Controllers;

use App\awvPatients;
use App\InvitationLink;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;


class AwvPatientsController extends Controller
{
    public function enterPhoneNumber()
    {
        /*$number  = '987.449.0587';
        $patient = awvPatients::where('number', $number)->first();

        return redirect(route('createSendUrl', [$patient]));*/
    }

    public function createSendUrl()
    {
        $number  = '590.788.0724 x7232';
        $patient = awvPatients::where('number', $number)->first();
        $url     = URL::temporarySignedRoute('loginSurvey', now()->addMinutes(1), ['patient' => $patient->id]);
        InvitationLink::create([
            'aw_patient_id' => $patient->id,
            'survey_id'     => '7',
            'link_token'    => $url,
            'is_expired'    => false,
        ]);

        //todo:send SM with $url here and then return feedback
        return 'invitation send';
    }

    public function authenticateInvitedUser(Request $request, $patient)
    {
        //todo: if link has expired click and give option to create another one.
        // Check if link has expired else expired the previous one an then create new//https://dev.to/fwartner/laravel-56---user-activation-with-signed-routes--notifications-oaa
        //else{

        if (! $request->hasValidSignature()) {
            return 'Your link has expired mate! i ll send a new one soon';
        }

        $patientUrlExp = InvitationLink::where('aw_patient_id', $patient)->get();
        foreach ($patientUrlExp as $url) {
            $url->update(['is_expired' => true]);
        }
        return 'Send User to Survey here';
    }

    public function authSurveyLogin()
    {
        $name      = 'Lauren Breitenberg';
        $birthDate = '1991-06-06';

        if ( ! User::where('name', $name)->first()) {
            return 'Name does not exists in our DB'; //todo:something more graceful here
        }
        if ( ! awvPatients::where('birth_date', $birthDate)->first()) {
            return 'Date Of Birth is Wrong';
        }

        return 'Login to survey';
    }
}
