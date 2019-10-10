<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendSurveyLinkRequest;
use App\NotifiableUser;
use App\Notifications\SurveyInvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\Services\SurveyService;
use App\Services\TwilioClientService;
use App\Survey;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvitationLinksController extends Controller
{
    private $service;
    private $surveyService;
    private $twilioService;

    public function __construct(
        SurveyInvitationLinksService $service,
        SurveyService $surveyService,
        TwilioClientService $twilioService
    ) {
        $this->service       = $service;
        $this->surveyService = $surveyService;
        $this->twilioService = $twilioService;
    }

    /**
     * Send HRA survey link to patient
     *
     * @param SendSurveyLinkRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendHraLink(SendSurveyLinkRequest $request)
    {
        try {
            $sent = $this->generateUrlAndSend($request, Survey::HRA);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ( ! $sent) {
            return response()->json(['error' => 'Could not send survey link'], 400);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Send Vitals link to practice staff
     *
     * @param SendSurveyLinkRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVitalsLink(SendSurveyLinkRequest $request)
    {
        try {
            $sent = $this->generateUrlAndSend($request, Survey::VITALS);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ( ! $sent) {
            return response()->json(['error' => 'Could not send survey link'], 400);
        }

        return response()->json(['message' => 'success']);
    }

    public function showEnrollUserForm(Request $request, $userId)
    {

        $patient = User::findOrFail($userId);

        return view('enrollUser', [
            'patientId'   => $patient->id,
            'patientName' => $patient->display_name,
        ]);
    }


    public function enrollUser(Request $request, $userId)
    {

        try {

            $forYear = Carbon::now()->year;

            $user = User
                ::with([
                    'surveyInstances' => function ($query) use ($forYear) {
                        $query->forYear($forYear);
                    },
                ])
                ->where('id', '=', $userId)
                ->firstOrFail();

            $this->service->enrolUser($user, $forYear);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['message' => 'success']);
    }

    public function showSendAssessmentLinkForm(Request $request, $userId, $surveyName, $channel)
    {
        $patient = User::findOrFail($userId);

        return view('sendAssessmentLink', [
            'patientId'   => $patient->id,
            'patientName' => $patient->display_name,
            'surveyName'  => $surveyName,
            'channel'         => $channel,
        ]);
    }

    /**
     * @param SendSurveyLinkRequest $request
     * @param string $surveyName
     *
     * @return string
     * @throws \Exception
     */
    private function generateUrlAndSend(
        SendSurveyLinkRequest $request,
        string $surveyName
    ) {
        $target_user_id = $request->get('target_patient_id');
        $channel        = $request->get('channel');
        $channelValue   = $request->get('channel_value');

        $forYear = Carbon::now()->year;

        $user = User
            ::with([
                'phoneNumbers',
                'patientInfo',
                'primaryPractice',
                'billingProvider',
                'surveyInstances' => function ($query) use ($surveyName, $forYear) {
                    $query->ofSurvey($surveyName)->forYear($forYear);
                },
            ])
            ->where('id', '=', $target_user_id)
            ->firstOrFail();

        $url = $this->service->createAndSaveUrl($user, $surveyName, $forYear, false);

        /** @var User $targetNotifiable */
        $targetNotifiable = null;

        if ($channel === 'sms') {
            $targetNotifiable = User
                ::whereHas('phoneNumbers', function ($q) use ($channelValue) {
                    $q->where('number', '=', $channelValue);
                })
                ->first();
        } else {
            $targetNotifiable = User::whereEmail($channelValue)->first();
        }

        if ( ! $targetNotifiable) {
            throw new \Exception("Could not find user[$channelValue] in the system.");
        }

        (new NotifiableUser($targetNotifiable, $channel === 'mail'
            ? $channelValue
            : null, $channel === 'sms'
            ? $channelValue
            : null))
            ->notify(new SurveyInvitationLink($url, $surveyName, $channel, ! $targetNotifiable->is($user) ? $user : null));

        return true;
    }
}
