<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Support\ScheduledTasks\Tasks;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;

class ClosureTask extends Task
{
    public static function canHandleEvent(Event $event): bool
    {
        if ( ! $event instanceof CallbackEvent) {
            return false;
        }

        return in_array($event->getSummaryForDisplay(), ['Closure', 'Callback']);
    }

    public function defaultName(): ?string
    {
        return null;
    }

    public function type(): string
    {
        return 'closure';
    }
}
