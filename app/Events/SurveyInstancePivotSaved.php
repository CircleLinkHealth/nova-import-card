<?php

namespace App\Events;

use App\SurveyInstance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SurveyInstancePivotSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $surveyInstance;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(SurveyInstance $surveyInstance)
    {
        $this->surveyInstance = $surveyInstance;
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
