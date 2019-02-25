<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswer;
use App\Services\SurveyService;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getSurvey(Request $request)
    {
        if ( ! $request->has('survey_id')) {
            return response()->json(['errors' => 'Request needs survey id'], 400);
        }
        //change auth user id
        $data = $this->service->getSurveyData(auth()->user()->id, $request->survey_id);

        if ( ! $data) {
            return response()->json(['errors' => 'Data not found'], 400);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
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
