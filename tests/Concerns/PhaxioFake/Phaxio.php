<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\PhaxioFake;

use App\Contracts\Efax;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class Phaxio extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake()
    {
        static::swap($fake = new PhaxioFake(new ConsoleLogger(new ConsoleOutput())));

        return $fake;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Efax::class;
    }
}
