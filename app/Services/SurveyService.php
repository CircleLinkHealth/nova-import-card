<?php

namespace App\Services;


use App\Answer;
use App\Events\SurveyInstancePivotSaved;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;

class SurveyService
{

    public static function getCurrentSurveyData($patientId, $surveyName)
    {
        $surveyId = Survey::where('name', '=', $surveyName)->pluck('id')->first();

        return self::getSurveyData($patientId, $surveyId);
    }

    public static function getSurveyData($patientId, $surveyId)
    {
        $patientWithSurveyData = User
            ::with([
                'billingProvider.user',
                'primaryPractice',
                'surveyInstances' => function ($instance) use ($surveyId) {
                    $instance->current()
                             ->wherePivot('survey_id', $surveyId)
                             ->with([
                                 'survey',
                                 'questions' => function ($question) {
                                     $question->with(['questionGroup', 'type.questionTypeAnswers']);
                                 },
                             ]);

                },
                'answers'         => function ($answer) use ($surveyId) {
                    $answer->whereHas('surveyInstance', function ($instance) use ($surveyId) {
                        $instance->where('survey_id', $surveyId)
                                 ->current();
                    });
                },
                'patientAWVSummaries',
            ])
            ->whereHas('surveys', function ($survey) use ($surveyId) {
                $survey->where('survey_id', $surveyId);
                       //->where('status', '!=', SurveyInstance::COMPLETED);
            })
            ->whereHas('surveyInstances', function ($instance) use ($surveyId) {
                $instance->where('users_surveys.survey_id', $surveyId);
                $instance->current();
            })
            ->findOrFail($patientId);

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

        $isComplete = isset($input['survey_complete']) && $input['survey_complete'];

        return SurveyService::updateSurveyInstanceStatus($input, $isComplete);

    }

    /**
     * Update the status of a survey based on answered questions
     *
     * @param $input
     *
     * @param $isComplete
     *
     * @return string Status of survey
     */
    public static function updateSurveyInstanceStatus($input, $isComplete)
    {
        $user = User
            ::with([
                'surveyInstances' => function ($instance) use ($input) {
                    $instance->where('survey_instances.id', $input['survey_instance_id']);
                },
            ])
            ->findOrFail($input['user_id']);

        $instance = $user->surveyInstances->first();

        if ( ! $instance->pivot->start_date) {
            $instance->pivot->start_date = Carbon::now();
        }

        //change status only if not completed
        if ($instance->pivot->status !== SurveyInstance::COMPLETED) {
            $instance->pivot->status = $isComplete
                ? SurveyInstance::COMPLETED
                : SurveyInstance::IN_PROGRESS;
        }

        $instance->pivot->last_question_answered_id = $input['question_id'];


        if ($isComplete) {
            $instance->pivot->completed_at = Carbon::now();
        }

        //save and then dispatch the event
        $instance->pivot->save();

        if ($isComplete || $instance->pivot->status === SurveyInstance::COMPLETED) {
            event(new SurveyInstancePivotSaved($instance));
        }

        return $instance->pivot->status;
    }

    private static function updateOrCreatePatientAWVSummary(User $patient)
    {
        if ( ! $patient) {
            return;
        }

        //fixme: this can break, what if user starts AWV on last day of month and completes next month?
        $date = Carbon::now();

        $isInitial = $patient->patientAWVSummaries->count() === 0;

        $summary = $patient->patientAWVSummaries->where('year', $date->year)->first();

        if ( ! $summary) {
            $patient->patientAWVSummaries()->create([
                'year'             => $date->year,
                'is_initial_visit' => $isInitial,
            ]);
        }
    }
}
