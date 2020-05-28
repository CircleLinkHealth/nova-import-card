<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;

class ReimportCcda extends Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Re-import';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Boolean::make('Clear Existing Data', 'clear')->trueValue('on'),
            Boolean::make('Without Transaction', 'without_transaction')->trueValue('on'),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->pluck('patient_user_id')->filter()->values()->each(function ($patientUserId) use ($fields) {
            $args = [];
            if ('on' === $fields->clear) {
                $args['--clear'] = true;
            }
            if ('on' === $fields->without_transaction) {
                $args['--without-transaction'] = true;
            }
            ReimportPatientMedicalRecord::for($patientUserId, auth()->id(), 'queue', $args);
        });

        return Action::message('CCDAs queued to reimport. We will send you a notification in CPM when done.');
    }
}
