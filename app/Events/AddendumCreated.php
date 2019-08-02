<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;

class AddendumCreated extends PusherEvent
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notifications'.$this->getPatientId());
    }

    public function getPatientId(): int
    {
        return 13267;
    }

    /**
     * @return array
     */
    public function receivers(): array
    {
        return [13238];
    }
}
