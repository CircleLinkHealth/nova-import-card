<?php

namespace App\Http\Controllers;

use App\AwvUser;
use App\InvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class InvitationLinksController extends Controller
{
    const LINK_EXPIRES_IN_DAYS = 14;
    private $service;

    public function __construct(SurveyInvitationLinksService $service)
    {
        $this->service = $service;
    }

    public function enterPatientForm()
    {
        return view('invitationLink.enterPatientForm');
    }

    public function sendInvitationLink(Request $request)
    {//@todo:should validate using more conditions

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $userId      = $request->get('id');
        $url         = $this->service->createAndSaveUrl($userId);
        $phoneNumber = $this->service->getPatientPhoneNumberById($userId);

        //@todo:HERE - send SMS using Twilio with $url and then return feedback

        return 'Invitation has been sent';
    }

    public function surveyFormAuth(Request $request, $userId)
    {
        $urlWithToken = $request->getRequestUri();

        return view('surveyUrlAuth.surveyFormAuth', compact('userId', 'urlWithToken'));
    }

    public function resendUrl($userId)
    {
        $this->service->createAndSaveUrl($userId);

        return 'New link is on its way';
    }

    public function authSurveyLogin(Request $request, $userId)
    {
        $name      = $request->input(['name']);
        $birthDate = $request->input(['date_of_birth']);
        $url       = $request->input(['url']);

        if ( ! User::where('name', $name)->first()) {
            return 'Name does not exists in our DB';
        }
        if ( ! AwvUser::where('birth_date', $birthDate)->first()) {
            return 'Date Of Birth is Wrong';
        }

        $urlToken = $this->service->parseUrl($url);

        $invitationLink = InvitationLink::where('awv_user_id', $userId)
                                        ->where('link_token', $urlToken)
                                        ->firstOrFail();

        $urlUpdatedAt = $invitationLink->updated_at;
        $isExpiredUrl = $invitationLink->is_manually_expired;
        $today        = now();
        $expireRange  = InvitationLinksController::LINK_EXPIRES_IN_DAYS;

        if ($isExpiredUrl || $urlUpdatedAt->diffInDays($today) > $expireRange) {
            $invitationLink->where('is_manually_expired', '=', 0)
                           ->update(['is_manually_expired' => true]);
            return view('surveyUrlAuth.resendUrl', compact('userId'));
        }

        return view('surveyQuestionnaire.survey');
    }

}
