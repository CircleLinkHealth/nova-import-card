<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\CcmBilling\Jobs\ExportPatientProblemCodes\ExportPatientProblemCodes as Job;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ExportPatientProblemCodes extends Action
{
    use InteractsWithQueue;
    use Queueable;

    protected $filename;

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * Perform the action on the given models.
     *
     * @throws \Exception
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        Job::dispatch($models->pluck('id')->toArray(), auth()->user()->id)
            ->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));

        return Action::message('Generating Patient Problem Codes report. A link to the report will be sent via email when job is completed.');
    }
}
