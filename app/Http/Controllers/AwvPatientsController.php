<?php

namespace App\Http\Controllers;

use App\awvPatients;
use App\InvitationLink;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;


class AwvPatientsController extends Controller
{
    public function enterPhoneNumber(Request $request)
    {//this is temporary just to keep it running
        if ($request->has('phone_number')) {
            $number = $request['phone_number'];
        } else {
            $number = '987.449.0587';
        }
        return redirect(route('createSendUrl', [$number]));
    }

    public function createSendUrl($number)
    {
        $patient = awvPatients::where('number', $number)->first();
        //create a unique URL with patient id.
        $url     = URL::temporarySignedRoute('loginSurvey', now()->addWeeks(2),
            ['patient' => $patient->id]);
        //save url
        InvitationLink::create([
            'aw_patient_id' => $patient->id,
            //michalis how should i handle the survey_id? autoincrement?
            'survey_id'     => '8',
            'link_token'    => $url,
            'is_expired'    => false,
        ]);
        //todo:send SMS with $url here and then return feedback
        return 'invitation send';
    }

    public function authenticateInvitedUser(Request $request, $patient)
    {
        $incomingUrl = url()->full();
        //if link is expired
        if ( ! $request->hasValidSignature()) {
            InvitationLink::where('aw_patient_id', $patient)
                          ->where('link_token', $incomingUrl)
                          ->update(['is_expired' => true]);
            return view('surveyUrlAuth.resendUrl', compact('patient'));
        }
        return 'Direct user to Survey Here';
    }

    public function resendUrl($patient)
    {
        $resendTo = awvPatients::with([
            'url' => function ($expired) {
                //if previous URLs for this user are not expired then set it to expired
                $expired->where('is_expired', '=', 0)
                        ->update(['is_expired' => true]);
            },
        ])->where('id', $patient)
                               ->firstOrFail();

        $phoneNumber = $resendTo->number;

        return redirect(route('createSendUrl', [$phoneNumber]));
    }

    public function authSurveyLogin()
    {
        $name      = 'Lauren Breitenberg';
        $birthDate = '1991-06-06';
        // i ll  use validator here
        if ( ! User::where('name', $name)->first()) {
            return 'Name does not exists in our DB'; //todo:something more graceful here
        }
        if ( ! awvPatients::where('birth_date', $birthDate)->first()) {
            return 'Date Of Birth is Wrong';
        }
        return 'Login to survey';
    }
}
