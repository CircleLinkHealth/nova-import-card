<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Jobs\EnrollmentSeletiveInviteEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SelfEnrollmentManualInvite extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (Enrollee $model) {
            if (is_null($model->user_id)) {
                Log::warning("Enrollee [$model->id] has null user_id. this is unexpected at this point");

                return;
            }

            EnrollmentSeletiveInviteEnrollees::dispatch([$model->user_id]);
        });

        Action::message('Invites should have been sent. Please check invitation panel.');
    }
}
