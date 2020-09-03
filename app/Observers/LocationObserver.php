<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\CcmBilling\Jobs\GenerateLocationSummaries;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;

class LocationObserver
{
    /**
     * Listen to the Patient created event.
     */
    public function created(Location $location)
    {
        GenerateLocationSummaries::dispatch($location->id);
    }
}
