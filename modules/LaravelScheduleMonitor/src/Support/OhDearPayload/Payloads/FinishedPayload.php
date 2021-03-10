<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Support\OhDearPayload\Payloads;

use Illuminate\Support\Arr;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class FinishedPayload extends Payload
{
    public static function canHandle(MonitoredScheduledTaskLogItem $logItem): bool
    {
        return MonitoredScheduledTaskLogItem::TYPE_FINISHED === $logItem->type;
    }

    public function data(): array
    {
        return Arr::only($this->logItem->meta ?? [], [
            'runtime',
            'exit_code',
            'memory',
        ]);
    }

    public function url()
    {
        return "{$this->baseUrl()}/finished";
    }
}
