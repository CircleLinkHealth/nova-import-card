<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use App\Contracts\PatientEvent;
use Carbon\Carbon;
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
    public function handle(PatientEvent $event)
    {
        ProcessSinglePatientMonthlyServices::dispatch($event->getPatientId(), Carbon::now()->startOfMonth()->startOfDay());
    }
}
