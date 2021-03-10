<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class ModifyPatientActivityAction extends Action
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
            Select::make('Set new Chargeable Service', 'chargeable_service')
                ->required(true)
                ->options([
                    ChargeableService::CCM                     => 'CCM',
                    ChargeableService::GENERAL_CARE_MANAGEMENT => 'CCM (RHC/FQHC)',
                    ChargeableService::BHI                     => 'BHI',
                    ChargeableService::PCM                     => 'PCM',
                    ChargeableService::RPM                     => 'RPM',
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
        $cs          = $fields->get('chargeable_service');
        $activityIds = $models->pluck('id')->toArray();

        try {
            (new ModifyPatientActivity($cs, $activityIds))->execute();
        } catch (\Exception $e) {
            $this->markAsFailed($models->first(), $e);
        }

        $this->markAsFinished($models->first());
    }
}
