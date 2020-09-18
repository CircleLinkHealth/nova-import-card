<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use CircleLinkHealth\SharedModels\Entities\Note;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteFinalSaved
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    /**
     * @var Note
     */
    public $note;
    /**
     * @var array
     */
    public $params;

    /**
     * Create a new event instance.
     *
     * @param array $params
     */
    public function __construct(Note $note, $params = [])
    {
        $this->note   = $note;
        $this->params = $params;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
