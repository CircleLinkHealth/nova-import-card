<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Jobs\FaxPatientCarePlansToLocation;
use App\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class FaxApprovedCarePlans extends Action implements ShouldQueue
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
        $practice = $models->first();

        if ($models->count() > 1) {
            $this->markAsFailed(
                $practice,
                'Invalid number of practices. Action can be performed on 1 practice only.'
            );

            return;
        }

        try {
            $number   = formatPhoneNumberE164($fields->fax_number);
            $location = $practice->locations()->where('fax', $number)->first();

            if ( ! $location) {
                $this->markAsFailed(
                    $practice,
                    'Could not find a location with fax '.$number
                );

                return;
            }

            User::ofType('participant')->ofPractice($practice->id)
                ->whereHas('patientInfo', function ($info) {
                    $info->enrolled();
                })
                ->whereHas('carePlan', function ($cp) {
                    $cp->where('status', CarePlan::PROVIDER_APPROVED);
                })
                ->chunk(300, function ($patients) use ($location) {
                    FaxPatientCarePlansToLocation::dispatch($patients, $location);
                });
            $this->markAsFinished($practice);
        } catch (\Exception $exception) {
            $this->markAsFailed($practice, $exception);
            throw $exception;
        }
    }
}
