<?php

namespace App\Services;


use App\SurveyInstance;
use App\User;

class SurveyService
{
    public function getSurveyData($patientId, $surveyId)
    {
        //get latest instance
        $data = User::with(['surveys.instances.questions.type.questionTypeAnswers', 'answers'])
                    ->whereHas('surveys', function ($survey) use ($surveyId) {
                        $survey->where('survey_id', $surveyId)
                               ->where('status', '!=', SurveyInstance::COMPLETED);
                    })
                    ->where('id', $patientId)
                    ->first();

        //survey
        // --questions
        //    --question - order, type, possible anwers, conditions, user answers validation rules?

        return $data;

    }

    public function storeAnswer($input)
    {

        $answer = User::findOrFail($input['user_id'])->answers()->create($input);

        //change survey status?
        //change last question answered field

        return $answer;

    }

    //update?
}