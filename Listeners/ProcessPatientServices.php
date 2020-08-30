<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;

class ProcessPatientServices
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
    public function handle($event)
    {
        ProcessSinglePatientMonthlyServices::dispatch($event->getPatientId());
    }
}
