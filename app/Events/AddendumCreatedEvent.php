<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;

class AddendumCreatedEvent extends PusherEvent
{ //we are going to broadcast the notifications (will dismiss this event)
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notifications.'.$this->getPatientId());
    }

    public function getPatientId(): int
    {
        return 13251;
    }

    /**
     * @return array
     */
    public function receivers(): array
    {
        return [13244];
    }
}
