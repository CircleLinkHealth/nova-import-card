<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswer;
use App\Services\EnrolleesSurveyService;
use App\Services\SurveyService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class EnrolleeSurveyController extends Controller
{
    /**
     * @var SurveyService
     */
    private $enrolleesSurveyService;

    /**
     * EnrolleeSurveyController constructor.
     * @param EnrolleesSurveyService $enrolleesSurveyService
     */
    public function __construct(EnrolleesSurveyService $enrolleesSurveyService)
    {
        $this->enrolleesSurveyService = $enrolleesSurveyService;
    }

    /**
     * @param $patientId
     * @param $surveyId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSurvey($patientId, $surveyId)
    {
        $surveyData = $this->enrolleesSurveyService->getSurveyData($patientId, $surveyId);

        if (!$surveyData) {
            throw new \Error("Survey not found for patient " . $patientId);
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
}
