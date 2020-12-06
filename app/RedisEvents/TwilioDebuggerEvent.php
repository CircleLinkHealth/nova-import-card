<?php


namespace App\RedisEvents;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TwilioDebuggerEvent
{
    const CHANNEL = "twillio-debugger-log-created";
    private $id;

    /**
     * TwilioDebuggerEvent constructor.
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
