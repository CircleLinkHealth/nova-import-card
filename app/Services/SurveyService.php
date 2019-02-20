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

    public function updateOrCreateAnswer($input)
    {

        //update or create the answer
        $answer = Answer::updateOrCreate([
            'user_id'            => $input['user_id'],
            'survey_instance_id' => $input['survey_instance_id'],
            'question_id'        => $input['question_id'],
        ], [
            'question_type_answer_id' => array_key_exists('question_type_answer_id', $input)
                ? $input['question_type_answer_id']
                : null,
            'value_1'                 => $input['value_1'],
            'value_2'                 => array_key_exists('value_2', $input)
                ? $input['value_2']
                : null,
        ]);

        if ( ! $answer) {
            return false;
        }

        return $info = [
            'created'       => true,
            'survey_status' => $this->updateSurveyInstanceStatus($input),
        ];

    }

    private function updateSurveyInstanceStatus($input)
    {
        $user = User::with([
            'surveyInstances' => function ($instance) use ($input) {
                $instance
                    ->where('survey_instances.id', $input['survey_instance_id'])
                    ->withCount([
                        'questions' => function ($q) {
                            $q->notOptional();
                        },
                    ]);
            },
        ])
                    ->withCount([
                        'answers' => function ($a) {
                            $a->whereHas('question', function ($q) {
                                $q->notOptional();
                            });
                        },
                    ])
                    ->where('id', $input['user_id'])
                    ->firstOrFail();

        $instance = $user->surveyInstances->first();

        if ($instance->questions_count === $user->answers_count) {
            $instance->pivot->status = SurveyInstance::COMPLETED;
        }else{
            $instance->pivot->status = SurveyInstance::IN_PROGRESS;
        }
        $instance->pivot->last_question_answered_id = $input['question_id'];
        $instance->save();

        return $instance->pivot->status;
    }


}