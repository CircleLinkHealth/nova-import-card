<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class UserUnreachable extends Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $name = 'Mark as unreachable';

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
     * @return void
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $userIds = $models->pluck('id');
        Patient::whereIn('user_id', $userIds)->update(['ccm_status' => Patient::UNREACHABLE]);
        $models->each(function ($model) {
            $this->markAsFinished($model);
        });
    }
}
