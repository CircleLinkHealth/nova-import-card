<?php

namespace App\Http\Controllers;

use App\awvPatients;
use App\InvitationLink;
use App\User;
use Illuminate\Http\Request;
use App\Services\SurveyInvitationLinksService;


class InvitationLinksController extends Controller
{
    private $service;

    public function __construct(SurveyInvitationLinksService $service)
    {
        $this->service = $service;
    }
    public function enterPhoneNumber(Request $request)
    {//this is temporary just to keep it running.
        //todo:validate input
        if ($request->has('phone_number')) {
            $phoneNumber = $request['phone_number'];
        } else {
            $phoneNumber = '601.472.3673';
        }
//todo:encrypt the phone number
        return redirect(route('createSendUrl', [$phoneNumber]));
    }

    public function createSendUrl($phoneNumber)
    {
        $patient = $this->service->getPatientIdByPhoneNumber($phoneNumber);
        $this->service->checkPatientHasPastUrl($patient);
        $url = $this->service->createAndSaveUrl($patient);
        //todo:HERE - send SMS using Twilio with $url and then return feedback
        return 'Invitation has been send';
    }

    public function authenticateInvitedUser(Request $request, $patientId)
    {//when patient clicks on the url will be redirected here
        $incomingUrl = url()->full();
        //get patient by id
        $patient = InvitationLink::where('awv_patient_id', $patientId)
                                 ->where('link_token', $incomingUrl)
                                 ->firstOrFail();
        //check url if has past active url.
        //$request->hasValidSignature() -> checks if the url has expired(past 2 weeks) and if true set expired==true
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
        $phoneNumber   = $resendTo->number;
        return redirect(route('createSendUrl', [$phoneNumber]));
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
