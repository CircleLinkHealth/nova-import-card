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

class PreviewLetter extends Action
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
        return [];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $count  = $models->count();
        $userId = auth()->id();

        if ($count > 1) {
            return Action::message('Forbidden! Should not execute action for more than one letter.');
        }

        $practiceId = $models->first()->practice_id;

        return Action::openInNewTab(url("self-enrollment-review/$practiceId/$userId"));
    }
}
