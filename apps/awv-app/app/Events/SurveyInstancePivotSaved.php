<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use App\SurveyInstance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyInstancePivotSaved
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $surveyInstance;

    /**
     * Create a new event instance.
     */
    public function __construct(SurveyInstance $surveyInstance)
    {
        $this->surveyInstance = $surveyInstance;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
    }
}
