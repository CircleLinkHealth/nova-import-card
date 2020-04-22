<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutoEnrollableCollected
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    /**
     * @var bool
     */
    public $isReminder;

    /**
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param bool $isReminder
     */
    public function __construct(User $user, $isReminder = false)
    {
        $this->user       = $user;
        $this->isReminder = $isReminder;
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
