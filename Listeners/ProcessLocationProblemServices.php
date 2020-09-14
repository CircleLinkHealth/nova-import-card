<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;

class ProcessLocationProblemServices
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
        //dispatch job
    }
}
