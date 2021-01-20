<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\CpmAdmin\Actions\ModifyPatientTime;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;

class ModifyPatientTimeAction extends Action implements ShouldQueue
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
            Select::make('Chargeable Service', 'chargeable_service')
                ->required(true)
                ->options([
                    ChargeableService::CCM                     => 'CCM',
                    ChargeableService::GENERAL_CARE_MANAGEMENT => 'CCM (RHC/FQHC)',
                    ChargeableService::BHI                     => 'BHI',
                    ChargeableService::PCM                     => 'PCM',
                    ChargeableService::RPM                     => 'RPM',
                ]),

            Number::make('Enter new duration (minutes)', 'durationMinutes')
                ->required(true),

            Boolean::make('Force (even if less than 20 minutes)', 'allow_accrued_towards'),
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
            $this->markAsFailed($models->first(), 'Please run this action for a single patient at a time.');

            return;
        }

        try {
            (new ModifyPatientTime(
                $models->first()->id,
                $fields->get('chargeable_service'),
                $fields->get('durationMinutes') * 60,
                $fields->get('allow_accrued_towards', false)
            ))->execute();
        } catch (\Exception $e) {
            $this->markAsFailed($models->first(), $e);
        }

        $this->markAsFinished($models->first());
    }
}
