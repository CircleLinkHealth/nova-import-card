<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendSurveyLinkRequest;
use App\Services\SurveyInvitationLinksService;
use App\Services\SurveyService;
use App\Services\TwilioClientService;
use App\Survey;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Twilio\Exceptions\TwilioException;

class InvitationLinksController extends Controller
{
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

    /**
     * todo: deprecate
     *
     * @param Request $request
     * @param TwilioClientService $twilioClientService
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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
        $user   = User
            ::with([
                'phoneNumbers',
                'patientInfo',
                'primaryPractice',
                'billingProvider',
                'surveyInstances' => function ($query) {
                    $query->ofSurvey(Survey::HRA)->current();
                },
            ])
            ->where('id', '=', $userId)
            ->firstOrFail();

        try {
            $url = $this->service->createAndSaveUrl($user, Carbon::now()->year);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => $e->getMessage()])
                ->withInput();
        }


        $phoneNumber = $user->phoneNumbers->first();
        $text        = $this->service->getSMSText($user, $url);
        $resp        = $this->sendSms($twilioClientService, $phoneNumber, $text);

        if ( ! $resp) {
            return back()
                ->withErrors(['message' => 'Could not send SMS'])
                ->withInput();
        }

        return back()->with(['message' => 'success']);
    }

    /**
     * Send HRA survey link to patient
     *
     * @param SendSurveyLinkRequest $request
     * @param TwilioClientService $twilioClientService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendHraLink(SendSurveyLinkRequest $request, TwilioClientService $twilioClientService)
    {
        $target_user_id = $request->get('target_patient_id');
        $channel        = $request->get('channel');
        $channelValue   = $request->get('channel_value');

        $user = User
            ::with([
                'phoneNumbers',
                'patientInfo',
                'primaryPractice',
                'billingProvider',
                'surveyInstances' => function ($query) {
                    $query->ofSurvey(Survey::HRA)->current();
                },
            ])
            ->where('id', '=', $target_user_id)
            ->firstOrFail();

        try {
            $url = $this->service->createAndSaveUrl($user, Carbon::now()->year, false);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        if ($channel === 'sms') {
            $text = $this->service->getSMSText($user, $url);
            $resp = $this->sendSms($twilioClientService, $channelValue, $text);
        } else {
            $resp = $this->sendEmail();
        }

        if ( ! $resp) {
            return response()->json(['error' => 'Could not send survey link']);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Send Vitals link to practice staff
     *
     * @param SendSurveyLinkRequest $request
     */
    public function sendVitalsLink(SendSurveyLinkRequest $request)
    {
        //if possible, validate that receiver is not a patient
    }

    private function sendSms(
        TwilioClientService $twilioService,
        string $phoneNumber,
        string $text
    ) {
        try {
            $messageId = $twilioService->sendSMS($phoneNumber, $text);
            //todo: save message id in db
        } catch (TwilioException $e) {
            return false;
        }

        return true;
    }

    private function sendEmail()
    {
        return true;
    }
}
