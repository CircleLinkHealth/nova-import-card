<?php

namespace App\Services;


use App\Answer;
use App\SurveyInstance;
use App\User;

class SurveyService
{
    public function getSurveyData($patientId, $surveyId)
    {
        $patientWithSurveyData = User::with([
            'surveyInstances' => function ($instance) use ($surveyId) {
                $instance->current()
                         ->wherePivot('survey_id', $surveyId)
                         ->with(['survey', 'questions.type.questionTypeAnswers']);
            },
            'answers',
        ])
                                     ->whereHas('surveys', function ($survey) use ($surveyId) {
                                         $survey->where('survey_id', $surveyId)
                                                ->where('status', '!=', SurveyInstance::COMPLETED);
                                     })
                                     ->whereHas('surveyInstances', function ($instance) use ($surveyId) {
                                         $instance->where('users_surveys.survey_id', $surveyId);
                                         $instance->current();
                                     })
                                     ->where('id', $patientId)
                                     ->first();


        return $patientWithSurveyData->toArray();

    }

    public function storeAnswer($input)
    {
        $user = User::findOrFail($input['user_id']);

        $answer = Answer::create($input);

        if ($answer) {
            $instance = $user->surveyInstances()->where('survey_instance_id', $input['survey_instance_id'])->first();

            if ($instance->pivot->status === SurveyInstance::PENDING ){
                //todo:add logic for complete, taking into account optional questions
                //count non optional questions for survey instance
                //find a way to see if user has answers for all of them. Where in?
                //maybe observer on answer model and from here send the survey instance status back ->use withCount
                $instance->pivot->status = SurveyInstance::IN_PROGRESS;
            }
            $instance->pivot->last_question_answered_id = $input['question_id'];
            $instance->save();

        }


        return $answer;

    }


}