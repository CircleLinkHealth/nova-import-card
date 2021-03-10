<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Tests;

class FakeJob
{
    public static $handled = false;

    public function handle()
    {
        static::$handled = true;
    }
}
