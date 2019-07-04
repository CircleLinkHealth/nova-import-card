<?php

namespace App\Http\Controllers;

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


        $resp = $this->sendSms($twilioClientService, $user, $url);

        if ( ! $resp) {
            return back()
                ->withErrors(['message' => 'Could not send SMS'])
                ->withInput();
        }

        return back()->with(['message' => 'success']);
    }

    public function sendSms(
        TwilioClientService $twilioService,
        User $user,
        string $url
    ) {
        $phoneNumber = $user->phoneNumbers->first();
        $text        = $this->service->getSMSText($user, $url);

        try {
            $messageId = $twilioService->sendSMS($phoneNumber, $text);
            //todo: save message id in db
        } catch (TwilioException $e) {
            return false;
        }

        return true;
    }
}
