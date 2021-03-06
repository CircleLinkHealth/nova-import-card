<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Support\OhDearPayload\Payloads;

use Illuminate\Support\Arr;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class FailedPayload extends Payload
{
    public static function canHandle(MonitoredScheduledTaskLogItem $logItem): bool
    {
        return MonitoredScheduledTaskLogItem::TYPE_FAILED === $logItem->type;
    }

    public function data(): array
    {
        return Arr::only($this->logItem->meta ?? [], [
            'failure_message',
        ]);
    }

    public function url()
    {
        return "{$this->baseUrl()}/failed";
    }
}
