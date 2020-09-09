<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use CircleLinkHealth\CcmBilling\Contracts\PatientEvent;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientUserCreated implements PatientEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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

    public function debounceDuration(): int
    {
        return 0;
    }

    public function getPatientId(): int
    {
        return $this->user->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function shouldDebounce(): bool
    {
        return false;
    }
}
