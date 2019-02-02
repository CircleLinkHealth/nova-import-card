<?php

namespace App\Http\Controllers;

use App\AwvPatients;
use App\InvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\User;
use Illuminate\Http\Request;


class InvitationLinksController extends Controller
{
    const link_expires_in_days = 14;
    private $service;

    public function __construct(SurveyInvitationLinksService $service)
    {
        $this->service = $service;
    }

    public function enterPhoneNumber(Request $request)
    {
        //@todo:create input form & validate input
        $phoneNumber = $request->get('phone_number');

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
        $resendTo = AwvPatients::where('id', $patientId)
                               ->firstOrFail();

        $phoneNumber = $resendTo->number;
        $this->createSendUrl($phoneNumber);

        return 'New link is on its way';
    }

    public function authSurveyLogin(Request $request, $patientId)
    {
        $name      = $request->input(['name']);
        $birthDate = $request->input(['date_of_birth']);
        $url       = $request->session()->previousUrl();

        //todo: use validator here
        if ( ! User::where('name', $name)->first()) {
            return 'Name does not exists in our DB';
        }
        if ( ! AwvPatients::where('birth_date', $birthDate)->first()) {
            return 'Date Of Birth is Wrong';
        }

        $urlToken = $this->service->parseUrl($url);
        $invitationLink = InvitationLink::where('awv_patient_id', $patientId)
                                        ->where('link_token', $urlToken)
                                        ->firstOrFail();

        $urlUpdatedAt = $invitationLink->updated_at;
        $isExpiredUrl = $invitationLink->is_expired;
        $today        = now();
        $expireRange  = InvitationLinksController::link_expires_in_days;

        if ( ! $urlUpdatedAt->diffInDays($today) < $expireRange || ! $isExpiredUrl == false) {
            $invitationLink->where('is_expired', '=', 0)->update(['is_expired' => true]);

            return view('surveyUrlAuth.resendUrl', compact('patientId'));
        }

        return 'Login to survey';
    }

}
