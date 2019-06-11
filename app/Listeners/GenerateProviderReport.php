<?php

namespace App\Listeners;

use App\Events\SurveyInstancePivotSaved;
use App\Jobs\GenerateProviderReport as GenerateReport;
use App\Jobs\PersonalizedPreventionPlan as GeneratePppReport;
use App\SurveyInstance;
use App\User;
class GenerateProviderReport  //@todo: shall we change this class since its now dispatching 2 reports(line 49-51)?
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
     * @param  SurveyInstancePivotSaved $event
     *
     * @return void
     */
    public function handle(SurveyInstancePivotSaved $event)
    {
        $instance = $event->surveyInstance;

        if ($instance->pivot->status === SurveyInstance::COMPLETED) {

            $patient = User::with([
                'surveyInstances' => function ($i) use ($instance) {
                    $i->where('start_date', $instance->start_date)
                      ->where('end_date', $instance->end_date)
                      ->where('survey_instances.survey_id', '!=', $instance->survey_id)
                      ->where('users_surveys.status', SurveyInstance::COMPLETED);
                },
            ])->find($instance->pivot->user_id);


            $otherInstance = $patient->surveyInstances->first();


            if ($otherInstance) {
                GenerateReport::dispatch($patient->id, $instance->start_date)->onQueue('high');
                GeneratePppReport::dispatch($patient->id, $instance->start_date)->onQueue('high');
            }
        }
    }
}
