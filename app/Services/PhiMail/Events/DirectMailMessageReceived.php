<?php

namespace App\Services\PhiMail\Events;

use App\DirectMailMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DirectMailMessageReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var DirectMailMessage
     */
    public $directMailMessage;
    
    /**
     * Create a new event instance.
     *
     * @param DirectMailMessage $directMailMessage
     */
    public function __construct(DirectMailMessage $directMailMessage)
    {
        $this->directMailMessage = $directMailMessage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
