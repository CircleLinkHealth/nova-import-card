<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UPG0506Handler implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle($event)
    {
        if ( ! $event->patient->hasCcda()) {
            return;
        }
        if ( ! $event->patient->latestCcda()->hasUPG0506PdfCareplanMedia()->exists()) {
            return;
        }
        $event->patient->carePlan->status               = CarePlan::PROVIDER_APPROVED;
        $event->patient->carePlan->provider_approver_id = optional($event->patient->billingProviderUser())->id;
        $event->patient->carePlan->save();
    }
}
