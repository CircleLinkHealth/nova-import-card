<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\CarePlanWasApproved;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCcdaStatus implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(CarePlanWasApproved $event)
    {
        Ccda::where('patient_id', $event->patient->id)->update([
            'status' => Ccda::CAREPLAN_CREATED,
        ]);
    }
}
