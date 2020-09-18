<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;

class MarkEnrolleesForAutoEnrollment extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $name = 'Mark Patients for Auto Enrollment';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Number::make('Mark subset of selected Patients (Input number)'),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $enrolleeIds = $models->pluck('id');

        Enrollee::whereIn('id', $enrolleeIds)
            ->update([
                'status'                  => Enrollee::QUEUE_AUTO_ENROLLMENT,
                'care_ambassador_user_id' => null,
                'attempt_count'           => 0,
                'requested_callback'      => null,
            ]);
    }
}
