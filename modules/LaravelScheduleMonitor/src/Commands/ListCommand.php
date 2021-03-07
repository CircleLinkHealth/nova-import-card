<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Commands;

use Illuminate\Console\Command;
use Spatie\ScheduleMonitor\Commands\Tables\DuplicateTasksTable;
use Spatie\ScheduleMonitor\Commands\Tables\MonitoredTasksTable;
use Spatie\ScheduleMonitor\Commands\Tables\ReadyForMonitoringTasksTable;
use Spatie\ScheduleMonitor\Commands\Tables\UnnamedTasksTable;

class ListCommand extends Command
{
    public $description = 'Display monitored scheduled tasks';
    public $signature   = 'schedule-monitor:list';

    public function handle()
    {
        (new MonitoredTasksTable($this))->render();
        (new ReadyForMonitoringTasksTable($this))->render();
        (new UnnamedTasksTable($this))->render();
        (new DuplicateTasksTable($this))->render();

        $this->line('');
    }
}
