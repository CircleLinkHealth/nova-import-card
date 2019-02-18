<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyAuthBeforeLoginRequest;
use App\InvitationLink;
use App\Services\SurveyInvitationLinksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;


class InvitationLinksController extends Controller
{
    const LINK_EXPIRES_IN_DAYS = 14;

    private $service;

    public function __construct(SurveyInvitationLinksService $service)
    {
        $this->service    = $service;
    }

    public function enterPatientForm()
    {
        return view('invitationLink.enterPatientForm');
    }

    public function createSendInvitationUrl(Request $request)
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

        $this->sendSms($phoneNumber, $url);

        return 'invitation has been sent';
    }

    public function sendSms($phoneNumber, $url)
    {
            $accountSid    = env('TWILIO_SID');
            $authToken     = env('TWILIO_TOKEN');

            try {
                $twilio = new Client($accountSid, $authToken);
            } catch (ConfigurationException $e) {

            }
            $message = $twilio->messages
                ->create($phoneNumber,
                    ["from" => "+1 646 759 2882", "body" => "Dr...... has invited you to complete a survey! Please enroll here:" .''. $url]
                );

            // print($message->sid);
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

    public function surveyAuthBeforeRedirect(SurveyAuthBeforeLoginRequest $request)
    {
        $urlToken       = $this->service->parseUrl($request->input('url'));
        $invitationLink = InvitationLink::with('patientInfo.user')
                                        ->where('link_token', $urlToken)
                                        ->firstOrFail();

        if ($invitationLink->patientInfo->birth_date != $request->input('birth_date')) {
            return 'Date of birth is wrong';
        }
        if ($invitationLink->patientInfo->user->display_name != $request->input('name')) {
            return 'Name does not exists in our DB';
        }
        $userId       = $invitationLink->patientInfo->user_id;
        $urlUpdatedAt = $invitationLink->updated_at;
        $isExpiredUrl = $invitationLink->is_manually_expired;
        $today        = now();
        $expireRange  = InvitationLinksController::LINK_EXPIRES_IN_DAYS;

        if ($isExpiredUrl || $urlUpdatedAt->diffInDays($today) > $expireRange) {
            $invitationLink->where('is_manually_expired', '=', 0)->update(['is_manually_expired' => true]);

            return view('surveyUrlAuth.resendUrl', compact('userId'));
        }

        return view('surveyQuestionnaire.survey', compact('urlToken'));
    }

}
