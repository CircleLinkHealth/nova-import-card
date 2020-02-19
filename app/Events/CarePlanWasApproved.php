<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Queue\SerializesModels;

class CarePlanWasApproved extends Event
{
    use SerializesModels;
    
    /**
     * @var User
     */
    public $patient;

    /**
     * Create a new event instance.
     *
     * @param User $patient
     */
    public function __construct(User $patient)
    {
        $this->patient          = $patient;
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
}
