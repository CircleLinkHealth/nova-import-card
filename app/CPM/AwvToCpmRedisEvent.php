<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CPM;

use App\Contracts\RedisEvent;
use Illuminate\Support\Facades\Redis;

abstract class AwvToCpmRedisEvent implements RedisEvent
{
    protected $channel;

    public function publish(array $data)
    {
        try {
            Redis::publish($this->channel,
                json_encode($data));
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
    }
}
