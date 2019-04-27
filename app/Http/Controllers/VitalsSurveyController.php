<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVitalsAnswer;
use App\Services\VitalsSurveyService;

class VitalsSurveyController extends Controller
{
    private $service;

    public function __construct(VitalsSurveyService $service)
    {
        $this->service = $service;
    }

    /**
     * Patient cannot access this route.
     * User must have `vitals-survey-complete` permission.
     *
     * @param $practiceId
     * @param $patientId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSurvey($practiceId, $patientId)
    {
        $userWithSurveyData = $this->service->getSurveyData($patientId);

        return view('survey.vitals.index', [
            'data' => $userWithSurveyData->toArray(),
        ]);
    }

    /**
     * Patient cannot access this route.
     * User must have `vitals-survey-complete` permission.
     *
     * @param StoreVitalsAnswer $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAnswer(StoreVitalsAnswer $request)
    {
        $answer = $this->service->updateOrCreateAnswer($request);

        if ( ! $answer) {
            return response()->json(['errors' => 'Answer was not created'], 400);
        }

        return response()->json([
            'created'       => true,
            'survey_status' => $answer,
        ], 200);

    }
}
