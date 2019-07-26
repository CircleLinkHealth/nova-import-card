<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddendumPusher implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $dataToPusher;

    /**
     * Create a new event instance.
     *
     * @param mixed $dataToPusher
     */
    public function __construct($dataToPusher)
    {
        $this->dataToPusher = $dataToPusher;
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('addendum.'.$this->dataToPusher['data']['receiver_id']);
    }
}
