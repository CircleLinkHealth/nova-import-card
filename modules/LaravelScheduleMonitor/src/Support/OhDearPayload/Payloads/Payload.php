<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Support\OhDearPayload\Payloads;

use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

abstract class Payload
{
    protected MonitoredScheduledTaskLogItem $logItem;

    public function __construct(MonitoredScheduledTaskLogItem $logItem)
    {
        $this->logItem = $logItem;
    }

    abstract public static function canHandle(MonitoredScheduledTaskLogItem $logItem): bool;

    abstract public function data();

    abstract public function url();

    protected function baseUrl(): string
    {
        return $this->logItem->monitoredScheduledTask->ping_url;
    }
}
