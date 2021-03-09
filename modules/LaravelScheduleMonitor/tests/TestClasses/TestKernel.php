<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Tests\TestClasses;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Orchestra\Testbench\Console\Kernel;

class TestKernel extends Kernel
{
    protected static array $registeredScheduleCommands = [];

    public static function clearScheduledCommands()
    {
        static::$registeredScheduleCommands = [];
    }

    public function commands()
    {
        return [
            FailingCommand::class,
        ];
    }

    public static function registerScheduledTasks(Closure $closure)
    {
        static::$registeredScheduleCommands[] = $closure;
    }

    public static function replaceScheduledTasks(Closure $closure)
    {
        static::clearScheduledCommands();
        static::$registeredScheduleCommands[] = $closure;
    }

    public function schedule(Schedule $schedule)
    {
        collect(static::$registeredScheduleCommands)->each(
            fn (Closure $closure) => $closure($schedule)
        );
    }
}
