<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface RedisEvent
{
    public function publish(array $data);
}
