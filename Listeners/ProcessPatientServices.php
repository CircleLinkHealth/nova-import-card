<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientEvent;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;

class ProcessPatientServices
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(PatientEvent $event)
    {
        $job = new ProcessSinglePatientMonthlyServices(
            $event->getPatientId(),
            Carbon::now()->startOfMonth()->startOfDay()
        );

        $event->shouldDebounce() ? debounce($job, $event->debounceDuration()) : dispatch($job);
    }
}
