<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Http\Requests\GetSurvey;
use App\Services\SurveyService;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getSurvey(GetSurvey $request)
    {
        //change auth user id - what if provider logs in for vitals?
        $userWithSurveyData = $this->service->getSurveyData(auth()->user()->id, $request->survey_id);
        if ( ! $userWithSurveyData) {
            return response()->json(['errors' => 'Data not found'], 400);
        }

        return response()->json([
            'success' => true,
            'data'    => $userWithSurveyData->toArray(),
        ], 200);
    }

    //i have disabled storeAnswer since we are not using any auth scaffolding yet
    public function storeAnswer(/*StoreAnswer*/
        Request $request
    ) {

        $answer = $this->service->updateOrCreateAnswer($request->input());

        if ( ! $answer) {
            return response()->json(['errors' => 'Answer was not created'], 400);
        }

        return response()->json([
            'created'       => true,
            'survey_status' => $answer,
        ], 200);

    }

    public function getPreviousAnswer($questionId, $userId)
    {
        $previousQuestionAnswer = Answer::where('question_id', $questionId)
                                        ->where('user_id', $userId)->first();

        return response()->json([
            'success'                => true,
            'previousQuestionAnswer' => $previousQuestionAnswer->value,
        ], 200);
    }
}