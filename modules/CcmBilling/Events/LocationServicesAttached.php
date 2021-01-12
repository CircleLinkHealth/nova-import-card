<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Events;

use Illuminate\Queue\SerializesModels;

class LocationServicesAttached
{
    use SerializesModels;

    protected int $locationId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $locationId)
    {
        $this->locationId = $locationId;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }
}
