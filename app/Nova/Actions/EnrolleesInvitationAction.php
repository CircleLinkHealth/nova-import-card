<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class EnrolleesInvitationAction extends Action
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
        if ($models->count() > 1) {
            // Allowing just one $model from AutoEnrollmentInvitationsPanel
            return Action::danger('Please select just one Practice!');
        }

        return Action::push('/resources/enrollees-invitation-panels', [
            'practice_id' => $models->first()->id,
        ]);
    }
}
