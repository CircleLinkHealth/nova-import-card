<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;

class LocationObserver
{
    /**
     * Listen to the Patient created event.
     */
    public function created(Location $location)
    {
        //todo: finialize decision
        sendSlackMessage('#channel-to-decide', "New Location with ID:$location->id failed.
            Please head to Location Chargeable Service management and assign chargeable services this location.");
    }
}
