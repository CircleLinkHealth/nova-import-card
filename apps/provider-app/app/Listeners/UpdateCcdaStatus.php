<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Customer\Events\CarePlanWasApproved;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCcdaStatus implements ShouldQueue, ShouldBeEncrypted
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
