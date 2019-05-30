<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Notifications\DisputeResolved;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class ResolveInvoiceDispute extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Resolution Note', 'resolution_note'),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection    $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $resolvedDateTime = Carbon::now();
        foreach ($models as $model) {
            $model->update(
                [
                    'resolved_by'     => auth()->id(),
                    'resolution_note' => $fields->resolution_note,
                    'resolved_at'     => $resolvedDateTime,
                    'is_resolved'     => true,
                ]
            );

            optional($model->user()->first())->notify(new DisputeResolved($model));
            $this->markAsFinished($model);
        }

        return Action::message('Dispute(s) resolved!');
    }
}
