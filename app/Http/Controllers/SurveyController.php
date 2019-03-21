<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetSurvey;
use App\Http\Requests\StoreAnswer;
use App\Services\SurveyService;

class SurveyController extends Controller
{
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getSurvey(GetSurvey $request)
    {
        //change auth user id
        $userWithSurveyData = $this->service->getSurveyData(auth()->user()->id, $request->survey_id);

        if ( ! $userWithSurveyData) {
            return response()->json(['errors' => 'Data not found'], 400);
        }

        return response()->json([
            'success' => true,
            'data'    => $userWithSurveyData->toArray(),
        ], 200);
    }

    public function storeAnswer(StoreAnswer $request)
    {
        $answer = $this->service->updateOrCreateAnswer($request->input());

        if ( ! $answer) {
            return response()->json(['errors' => 'Answer was not created'], 400);
        }

        return response()->json([
            'created'       => true,
            'survey_status' => $answer,
        ], 200);

    }
}