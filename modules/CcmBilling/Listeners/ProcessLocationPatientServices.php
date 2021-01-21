<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;
use CircleLinkHealth\CcmBilling\Jobs\ProcessLocationPatientMonthlyServices;

class ProcessLocationPatientServices
{
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
     * @param  object $event
     * @return void
     */
    public function handle(LocationServicesAttached $event)
    {
        ProcessLocationPatientMonthlyServices::dispatch($event->getLocationId(), Carbon::now()->startOfMonth()->startOfDay());
    }
}
