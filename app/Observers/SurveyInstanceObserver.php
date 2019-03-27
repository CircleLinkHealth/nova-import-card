<?php


namespace App\Observers;


use App\Jobs\GenerateProviderReport;
use App\SurveyInstance;

class SurveyInstanceObserver
{
    /**
     * Listen to the Instance saved event.
     *
     * @param SurveyInstance $instance
     */
    public function saved(SurveyInstance $instance)
    {

        $x =1;
        if ($instance->pivot->status === SurveyInstance::COMPLETED) {
            $otherInstance = SurveyInstance::isCompleted()
                                           ->where('user_id', $instance->user_id)
                                           ->where('start_date', $instance->start_date)
                                           ->where('end_date', $instance->end_date)
                                           ->where('survey_id', '!=', $instance->survey_id)
                                           ->first();

            if ($otherInstance) {
                GenerateProviderReport::dispatch($instance->user_id, $instance->start_date)->onQueue('high');
            }
        }

    }
}