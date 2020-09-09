<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use App\Constants;
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
        debounce(
            new ProcessSinglePatientMonthlyServices(
                $event->getPatientId(),
                Carbon::now()->startOfMonth()->startOfDay()
            ),
            Constants::FIVE_MINUTES_IN_SECONDS
        );
    }
}
