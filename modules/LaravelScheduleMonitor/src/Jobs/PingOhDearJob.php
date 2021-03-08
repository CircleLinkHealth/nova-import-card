<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;
use Spatie\ScheduleMonitor\Support\OhDearPayload\OhDearPayloadFactory;

class PingOhDearJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    public $deleteWhenMissingModels = true;

    public MonitoredScheduledTaskLogItem $logItem;

    public function __construct(MonitoredScheduledTaskLogItem $logItem)
    {
        $this->logItem = $logItem;

        if ($queue = config('schedule-monitor.oh_dear.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle()
    {
        if ( ! $payload = OhDearPayloadFactory::createForLogItem($this->logItem)) {
            return;
        }

        $response = Http::post($payload->url(), $payload->data());
        $response->throw();

        $this->logItem->monitoredScheduledTask->update(['last_pinged_at' => now()]);
    }
}
