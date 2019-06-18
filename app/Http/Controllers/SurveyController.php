<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Http\Requests\StoreAnswer;
use App\Services\SurveyService;
use Auth;

class SurveyController extends Controller
{
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getSurvey($practiceId, $patientId, $surveyId)
    {
        //no need to have this check here
        if ( ! Auth::check()) {
            return redirect()->route('survey.vitals.welcome', ['practiceId' => $practiceId, 'patientId' => $patientId]);
        }

//        if (!Auth::user()->hasPermissionForSite('vitals-survey-complete', $practiceId)) {
//            return redirect()->route('survey.vitals.not.authorized', ['practiceId' => $practiceId, 'patientId' => $patientId]);
//        }

        $surveyData = $this->service->getSurveyData($patientId, $surveyId);

        if ( ! $surveyData) {
            throw new \Error("Survey not found for patient " . $patientId);
        }

        return view('survey.hra.index', [
            'data' => $surveyData->toArray(),
        ]);
    }

    public function storeAnswer(StoreAnswer $request)
    {
        $input            = $request->all();
        $input['user_id'] = $input['patient_id'];

        $answer = $this->service->updateOrCreateAnswer($input);

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
