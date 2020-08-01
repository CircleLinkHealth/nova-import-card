<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use App\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallIsReadyForAttestedProblemsAttachment
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var array
     */
    protected $attestedProblems;

    /**
     * @var Call
     */
    protected $call;

    /**
     * Create a new event instance.
     */
    public function __construct(Call $call, array $attestedProblems)
    {
        $this->call             = $call;
        $this->attestedProblems = $attestedProblems;
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

    public function getCall()
    {
        return $this->call;
    }

    public function getProblems()
    {
        return $this->attestedProblems;
    }
}
