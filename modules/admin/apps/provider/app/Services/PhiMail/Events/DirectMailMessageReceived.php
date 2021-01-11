<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Events;

use App\DirectMailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DirectMailMessageReceived
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    /**
     * @var DirectMailMessage
     */
    public $directMailMessage;

    /**
     * Create a new event instance.
     */
    public function __construct(DirectMailMessage $directMailMessage)
    {
        $this->directMailMessage = $directMailMessage;
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
