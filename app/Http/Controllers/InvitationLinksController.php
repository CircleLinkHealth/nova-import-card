<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyAuthLoginRequest;
use App\InvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\Services\SurveyService;
use App\Services\TwilioClientService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Twilio\Exceptions\TwilioException;


class InvitationLinksController extends Controller
{
    const LINK_EXPIRES_IN_DAYS = 14;

    private $service;
    private $surveyService;

    public function __construct(SurveyInvitationLinksService $service, SurveyService $surveyService)
    {
        $this->service       = $service;
        $this->surveyService = $surveyService;
    }

    public function enterPatientForm()
    {
        return view('invitationLink.enterPatientForm');
    }

    public function createSendInvitationUrl(Request $request, TwilioClientService $twilioClientService)
    {
        //@todo:should validate using more conditions
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $userId = $request->get('id');

        try {
            $url = $this->service->createAndSaveUrl($userId, Carbon::now()->year);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => $e->getMessage()])
                ->withInput();
        }

        $phoneNumber = $this->service->getPatientPhoneNumberById($userId);

        //todo: need provider name
        $resp = $this->sendSms($twilioClientService, '....', $phoneNumber, $url);

        if ( ! $resp) {
            return back()
                ->withErrors(['message' => 'Could not send SMS'])
                ->withInput();
        }

        return back()->with(['message' => 'success']);
    }

    public function sendSms(TwilioClientService $twilioService, string $providerName, string $phoneNumber, string $url)
    {
        $text = "Dr $providerName has invited you to complete a survey! Please enroll here: $url";

        try {
            $messageId = $twilioService->sendSMS($phoneNumber, $text);
            //todo: save message id in db
        } catch (TwilioException $e) {
            return false;
        }

        return true;
    }


    public function surveyLoginForm(Request $request, $userId)
    {
        $urlWithToken = $request->getRequestUri();

        $user            = User::with(['primaryPractice', 'billingProvider'])->where('id', '=', $userId)->firstOrFail();
        $practiceName    = $user->getPrimaryPracticeName();
        $doctorsLastName = $user->billingProviderUser()->display_name;

        return view('surveyUrlAuth.surveyLoginForm',
            compact('userId', 'urlWithToken', 'practiceName', 'doctorsLastName'));
    }

    public function surveyLoginAuth(SurveyAuthLoginRequest $request)
    {
        $urlToken = $this->service->parseUrl($request->input('url'));

        /** @var InvitationLink $invitationLink */
        $invitationLink = InvitationLink::with('patientInfo.user')
                                        ->where('link_token', $urlToken)
                                        ->firstOrFail();

        if ($invitationLink->patientInfo->birth_date != $request->input('birth_date')) {
            return back()
                //since this is a login form, we need to send general messages
                ->withErrors(['message' => 'The data you entered does not match our records.'])
                ->withInput();
        }

        if ($invitationLink->patientInfo->user->display_name != $request->input('name')) {
            return back()
                //since this is a login form, we need to send general messages
                ->withErrors(['message' => 'The data you entered does not match our records.'])
                ->withInput();
        }

        $userId       = $invitationLink->patientInfo->user_id;
        $surveyId     = $invitationLink->survey_id;
        $urlUpdatedAt = $invitationLink->updated_at;
        $isExpiredUrl = $invitationLink->is_manually_expired;
        $today        = now();
        $expireRange  = InvitationLinksController::LINK_EXPIRES_IN_DAYS;

        if ($isExpiredUrl || $urlUpdatedAt->diffInDays($today) > $expireRange) {

            if ( ! $isExpiredUrl) {
                $invitationLink->is_manually_expired = true;
                $invitationLink->save();
            }

            return back()
                ->withErrors(['expired_link' => 'Your link has expired. Please request a new one.']);
        }

        return redirect()->route('survey.hra',
            [
                'practiceId' => $invitationLink->patientInfo->user->program_id,
                'patientId'  => $userId,
                'surveyId'   => $surveyId,
            ]);
    }

}
