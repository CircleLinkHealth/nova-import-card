<?php

namespace App\Http\Controllers;

use App\awvPatients;
use App\InvitationLink;
use App\User;
use Illuminate\Http\Request;



class AwvPatientsController extends Controller
{
    public function enterPhoneNumber(Request $request)
    {//this is temporary just to keep it running
        if ($request->has('phone_number')) {
            $number = $request['phone_number'];
        } else {
            $number = '549.807.4908';
        }

        return redirect(route('createSendUrl', [$number]));
    }

    public function authenticateInvitedUser(Request $request, $patient)
    {
        $incomingUrl = url()->full();
        //if link is expired
        $patient = InvitationLink::where('aw_patient_id', $patient)
                                 ->where('link_token', $incomingUrl)
                                 ->firstOrFail();

        $hasOldValidUrl = $patient->is_expired;
        if ( ! $request->hasValidSignature() || ! $hasOldValidUrl == false) {
            $patient->where('is_expired', '=', 0)->update(['is_expired' => true]);
            return view('surveyUrlAuth.resendUrl', compact('patient'));
        }
        return 'Direct user to Survey Here';
    }

    public function resendUrl($patient)
    {
        $resendTo = awvPatients::where('id', $patient)->firstOrFail();
        $number   = $resendTo->number;
        return redirect(route('createSendUrl', [$number]));
    }

    public function authSurveyLogin()
    {//input name & DOB
        $name      = 'Lauren Breitenberg';
        $birthDate = '1991-06-06';
        //todo: use validator here
        if ( ! User::where('name', $name)->first()) {
            return 'Name does not exists in our DB'; //todo:something more graceful here
        }
        if ( ! awvPatients::where('birth_date', $birthDate)->first()) {
            return 'Date Of Birth is Wrong';
        }

        return 'Login to survey';
    }
}
