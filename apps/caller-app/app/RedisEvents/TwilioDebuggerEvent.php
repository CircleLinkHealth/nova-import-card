<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\RedisEvents;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TwilioDebuggerEvent
{
    const CHANNEL = 'twillio-debugger-log-created';
    private $id;

    /**
     * TwilioDebuggerEvent constructor.
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function publish()
    {
        try {
            Redis::publish(self::CHANNEL, $this->id);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
