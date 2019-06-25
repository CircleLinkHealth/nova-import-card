<?php

namespace App\Services;


use App\Answer;
use App\Events\SurveyInstancePivotSaved;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;

class SurveyService
{
    public static function getSurveyData($patientId, $surveyId)
    {
        //fixme: merge this with query below
        $user = User::with([
            'surveyInstances' => function ($instance) use ($surveyId) {
                $instance->current()
                         ->wherePivot('survey_id', $surveyId);
            },
        ])->find($patientId);

        if ( ! $user || $user->surveyInstances->isEmpty()) {
            return null;
        }

        $surveyInstanceId = $user->surveyInstances->first()->id;

        $patientWithSurveyData = User
            ::with([
                'billingProvider.user',
                'primaryPractice',
                'surveyInstances'     => function ($instance) use ($surveyId) {
                    $instance->current()
                             ->wherePivot('survey_id', $surveyId)
                             ->with([
                                 'survey',
                                 'questions' => function ($question) {
                                     $question->with(['questionGroup', 'type.questionTypeAnswers']);
                                 },
                             ]);

                },
                'answers'             => function ($answer) use ($surveyInstanceId) {
                    $answer->where('survey_instance_id', $surveyInstanceId);
                },
                'patientAWVSummaries'
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

        self::updateOrCreatePatientAWVSummary($patientWithSurveyData);

        return $patientWithSurveyData;

    }

    /**
     * Update or create an answer for a survey
     *
     * @param $input
     *
     * @return bool|string false if could not create/update answer, string for new survey status
     */
    public static function updateOrCreateAnswer($input)
    {
        $answer = Answer::updateOrCreate([
            'user_id'            => $input['user_id'],
            'survey_instance_id' => $input['survey_instance_id'],
            'question_id'        => $input['question_id'],
        ], [
            'question_type_answer_id' => array_key_exists('question_type_answer_id', $input)
                ? $input['question_type_answer_id']
                : null,
            'value'                   => $input['value'],
        ]);

        if ( ! $answer) {
            return false;
        }

        return SurveyService::updateSurveyInstanceStatus($input);

    }

    /**
     * Update the status of a survey based on answered questions
     *
     * @param $input
     *
     * @return string Status of survey
     */
    public static function updateSurveyInstanceStatus($input)
    {
        $user = User
            ::with([
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
                'answers' => function ($a) use ($input) {
                    $a->where('survey_instance_id', $input['survey_instance_id'])
                      ->whereHas('question', function ($q) {
                          $q->notOptional();
                      });
                },
            ])
            ->where('id', $input['user_id'])
            ->firstOrFail();

        $instance = $user->surveyInstances->first();

        if ($instance->questions_count <= $user->answers_count) {
            $instance->pivot->status = SurveyInstance::COMPLETED;
        } else {
            $instance->pivot->status = SurveyInstance::IN_PROGRESS;
        }

        $instance->pivot->last_question_answered_id = $input['question_id'];
        $instance->pivot->save();

        event(new SurveyInstancePivotSaved($instance));

        return $instance->pivot->status;
    }

    private static function updateOrCreatePatientAWVSummary($patient)
    {
        if (!$patient) {
            return;
        }

        $date = Carbon::now();

        $isInitial = $patient->patientAWVSummaries->count() === 0;

        $summary = $patient->patientAWVSummaries->where('month_year', $date->copy()->startOfMonth())->first();

        if ( ! $summary) {
            $patient->patientAWVSummaries()->create([
                'month_year'    => $date->copy()->startOfMonth(),
                'is_initial_visit' => $isInitial,
            ]);

            return;
        }

        return;
    }

}
