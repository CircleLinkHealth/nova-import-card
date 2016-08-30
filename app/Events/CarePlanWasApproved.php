<?php

namespace App\Events;

use App\Events\Event;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CarePlanWasApproved extends Event
{
    use SerializesModels;

    public $patient;

    /**
     * Create a new event instance.
     *
     * @param User $patient
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
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
