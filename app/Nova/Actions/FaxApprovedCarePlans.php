<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\CarePlan;
use App\Notifications\CarePlanProviderApproved;
use App\Notifications\Channels\FaxChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class FaxApprovedCarePlans extends Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

//    use SerializesModels;

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Fax Number'),
        ];
    }

    /**
     * Validate models and input,
     * then if location exists with the given Fax number, get enrolled practice patients with provider approved careplans and fax them to Practice.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() > 1) {
            $this->markAsFailed(
                $models->first(),
                'Invalid number of practices. Action can be performed on 1 practice only.'
            );

            return;
        }

        try {
            $practice = $models->first();
            $location = $practice->locations()->where('fax', $fields->fax_number)->first();

            if ( ! $location) {
                throw new \Exception('Invalid Fax Number.');
            }

            $practice->patients()
                ->whereHas('patientInfo', function ($info) {
                         $info->enrolled();
                     })
                ->whereHas('carePlan', function ($cp) {
                         $cp->where('status', CarePlan::PROVIDER_APPROVED);
                     })
                ->get()
                ->each(function ($patient) use ($location) {
                         $location->notify(new CarePlanProviderApproved($patient->carePlan, [FaxChannel::class]));
                     });
            $this->markAsFinished($practice);
        } catch (\Exception $exception) {
            $this->markAsFailed($practice, $exception);
        }
    }
}
