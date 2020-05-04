<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswer;
use App\Services\EnrolleesSurveyService;
use App\Services\SurveyInvitationLinksService;
use App\Services\SurveyService;
use App\Survey;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrolleeSurveyController extends Controller
{
    /**
     * @var SurveyService
     */
    private $enrolleesSurveyService;
    /**
     * @var SurveyInvitationLinksService
     */
    private $surveyInvitationLinksService;

    /**
     * EnrolleeSurveyController constructor.
     * @param EnrolleesSurveyService $enrolleesSurveyService
     * @param SurveyInvitationLinksService $surveyInvitationLinksService
     */
    public function __construct(EnrolleesSurveyService $enrolleesSurveyService, SurveyInvitationLinksService $surveyInvitationLinksService)
    {
        $this->enrolleesSurveyService = $enrolleesSurveyService;
        $this->surveyInvitationLinksService = $surveyInvitationLinksService;
    }

    /**
     * @param $patientId
     * @param $surveyId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSurvey($patientId, $surveyId)
    {
        $surveyData = $this->enrolleesSurveyService->getSurveyData($patientId, $surveyId);

        if (! $surveyData) {
            throw new \Error('Survey not found for patient '.$patientId);
        }

        return view('survey.Enrollees.index', [
            'data' => $surveyData->toArray(),
        ]);
    }

    public function storeAnswer(StoreAnswer $request)
    {
        $input = $request->all();
        $input['user_id'] = $input['patient_id'];

        return SurveyService::updateOrCreateAnswer($input);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEnrollableQuestionsData(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::with('patientInfo')
            ->where('id', $userId)
            ->firstOrFail();

        $data = $this->enrolleesSurveyService->enrolleesQuestionsData($user);

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    /**
     * @param $userId
     * @param $surveyId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function createEnrolleesSurveyUrl($userId, $surveyId)
    {
//        $user = User::whereId($userId)->firstOrFail();
//        $survey = Survey::whereId($surveyId)->firstOrFail();
        $user = User::findOrFail($userId);
        $survey = Survey::findOrFail($surveyId);
        $url = $this->surveyInvitationLinksService->createAndSaveUrl($user, $survey->name, true);
        Auth::loginUsingId($userId, true);
        return redirect($url);
    }
}
