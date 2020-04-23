<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ClearAndReimportCcda extends Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Clear and re-import';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    public static function for(int $patientUserId, ?int $notifiableUserId, string $method = 'queue'): void
    {
        Artisan::$method(
            ReimportPatientMedicalRecord::class,
            [
                'patientUserId'   => $patientUserId,
                'initiatorUserId' => $notifiableUserId,
                '--clear'         => true,
            ]
        );
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->pluck('patient_user_id')->filter()->values()->each(function ($patientUserId) {
            self::for($patientUserId, auth()->id(), 'queue');
        });

        return Action::message('CCDAs queued to reimport. We will send you a notification in CPM when done.');
    }
}
