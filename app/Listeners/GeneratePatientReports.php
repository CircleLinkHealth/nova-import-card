<?php

namespace App\Listeners;

use App\CPM\EnrollableCompletedSurvey;
use App\Events\SurveyInstancePivotSaved;
use App\Jobs\GeneratePatientReportsJob as GenerateReports;
use App\Survey;
use App\SurveyInstance;
use App\User;

class GeneratePatientReports
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param SurveyInstancePivotSaved $event
     *
     * @return void
     */
    public function handle(SurveyInstancePivotSaved $event)
    {
        $instance = $event->surveyInstance;
        $surveyName = Survey::whereId($event->surveyInstance->survey_id)->first()->name;
        if ($instance->pivot->status === SurveyInstance::COMPLETED) {
            $patient = User::with([
                'surveyInstances' => function ($i) use ($instance) {
                    $i->forYear($instance->year)
                        ->where('survey_instances.survey_id', '!=', $instance->survey_id)
                        ->where('users_surveys.status', SurveyInstance::COMPLETED);
                },
            ])->find($instance->pivot->user_id);

            if (Survey::ENROLLEES === $surveyName) {
                //Instantiate Redis Event class to emit report created events to CPM
                $redisSurveyCompletedEvent = new EnrollableCompletedSurvey($patient->id);
                $redisSurveyCompletedEvent->publishEnrollableCompletedSurvey($instance->id);
            } else {
                $otherInstance = $patient->surveyInstances->first();
                if ($otherInstance) {
                    GenerateReports::dispatch($patient->id, $instance->year)->onQueue('awv-high');
                }
            }
        }
    }
}
