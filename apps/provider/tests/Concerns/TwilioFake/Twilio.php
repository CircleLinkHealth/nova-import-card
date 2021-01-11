<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\TwilioFake;

use CircleLinkHealth\Core\TwilioInterface;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class Twilio extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake()
    {
        static::swap($fake = new TwilioFake(new ConsoleLogger(new ConsoleOutput())));

        return $fake;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TwilioInterface::class;
    }
}
