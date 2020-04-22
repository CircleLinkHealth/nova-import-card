<?php

namespace App\Events;

use App\SurveyInstance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyInstancePivotSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $surveyInstance;

    /**
     * Create a new event instance.
     *
     * @param SurveyInstance $surveyInstance
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
        //
    }
}
