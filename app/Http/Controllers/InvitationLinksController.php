<?php

namespace App\Http\Controllers;

use App\AwvPatients;
use App\InvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;


class InvitationLinksController extends Controller
{
    private $service;

    public function __construct(SurveyInvitationLinksService $service)
    {
        $this->service = $service;
    }

    public function enterPhoneNumber(Request $request)
    {//this is temporary just to keep it running.
        //@todo:validate input
        $phoneNumber= $request->get('phone_number', '1-836-432-4663 x56539');
        $this->createSendUrl($phoneNumber);
        return 'Invitation has been sent';
    }

    public function createSendUrl($phoneNumber)
    {
        $patientId = $this->service->getPatientIdByPhoneNumber($phoneNumber);
        $this->service->checkPatientHasPastUrl($patientId);
        $url = $this->service->createAndSaveUrl($patientId);
        //@todo:HERE - send SMS using Twilio with $url and then return feedback
    }

    public function surveyFormAuth($patientId)
    {
        return view('surveyUrlAuth.surveyFormAuth', compact('patientId'));
    }

    public function resendUrl($patientId)
    {
        $resendTo    = AwvPatients::where('id', $patientId)->firstOrFail();
        $phoneNumber = $resendTo->number;
        $this->createSendUrl($phoneNumber);
        return 'New link has is its way';
    }

    public function authSurveyLogin(Request $request, $patientId)
    {
        $name        = $request->input(['name']);
        $birthDate   = $request->input(['date_of_birth']);
        $incomingUrl = $request->input(['url']);
        $invitationLink = InvitationLink::where('awv_patient_id', $patientId)
                                 ->where('link_token', $incomingUrl)
                                 ->firstOrFail();

        $urlUpdatedAt    = $invitationLink->updated_at;
        $isExpiredUrl = $invitationLink->is_expired;
        $today = now();
        $expireAfter = 14;//days - @todo: not use magic number
        //todo: use validator here
        if ( ! User::where('name', $name)->first()) {
            return 'Name does not exists in our DB';
        }
        if ( ! AwvPatients::where('birth_date', $birthDate)->first()) {
            return 'Date Of Birth is Wrong';
        }
        if (! $urlUpdatedAt->diffInDays($today) < $expireAfter || ! $isExpiredUrl == false) {

            $invitationLink->where('is_expired', '=', 0)->update(['is_expired' => true]);
            return view('surveyUrlAuth.resendUrl', compact('patientId'));
        }
        return 'Login to survey';
    }


}
