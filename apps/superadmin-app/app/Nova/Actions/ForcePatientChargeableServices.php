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
use Laravel\Nova\Fields\Select;

class ForcePatientChargeableServices extends Action
{
    use InteractsWithQueue;
    use Queueable;

    protected ?int $patientId;

    public function __construct(int $patientId = null)
    {
        $this->patientId = $patientId;
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Forced Chargeable Services')->options([
                $this->patientId => $this->patientId,
            ]),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach (collect($fields->get('chargeable_service_id')) as $csId) {
            //force or unforce
        }
    }
}
