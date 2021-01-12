<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\UnresolvedPostmarkCallback;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;

class ArchiveUnresolvedCallback extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Archive callback';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Boolean::make('Archive', 'archive')->trueValue('on')->default('on'),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $value = false;

        if ($fields->get('archive')) {
            $value = true;
        }

        $ids = $models->pluck('postmark_id');
        UnresolvedPostmarkCallback::whereIn('postmark_id', $ids)
            ->update([
                'manually_resolved' => $value,
            ]);

        Action::message('Selected items archived!');
    }
}
