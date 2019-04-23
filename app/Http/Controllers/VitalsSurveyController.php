<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetVitalsSurvey;
use App\Http\Requests\StoreVitalsAnswer;
use App\Services\VitalsSurveyService;

class VitalsSurveyController extends Controller
{
    private $service;

    public function __construct(VitalsSurveyService $service)
    {
        $this->service = $service;
    }

    public function getSurvey(GetVitalsSurvey $request)
    {
        //change auth user id
        $userWithSurveyData = $this->service->getSurveyData($request->get('patient_id'));
        if ( ! $userWithSurveyData) {
            return response()->json(['errors' => 'Data not found'], 400);
        }

        return response()->json([
            'success' => true,
            'data'    => $userWithSurveyData->toArray(),
        ], 200);
    }

    //i have disabled storeAnswer since we are not using any auth scaffolding yet
    public function storeAnswer(StoreVitalsAnswer $request) {
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
