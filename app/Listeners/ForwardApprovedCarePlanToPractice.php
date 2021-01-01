<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Customer\Events\CarePlanWasProviderApproved;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ForwardApprovedCarePlanToPractice implements ShouldQueue, ShouldBeEncrypted
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(CarePlanWasProviderApproved $event)
    {
        $event->patient->carePlan->forward();
    }
}
