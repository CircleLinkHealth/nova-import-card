<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Tests\TestClasses;

use Exception;
use Illuminate\Console\Command;

class FailingCommand extends Command
{
    public static bool $executed = false;

    public $signature = 'failing-command';

    public function handle()
    {
        throw new Exception('failing');
    }
}
