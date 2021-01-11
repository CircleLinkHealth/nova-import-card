<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;

use CircleLinkHealth\SharedModels\Services\SchedulerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveScheduledCallsForUnenrolledPatients implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int[]
     */
    public array $patientUserIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $patientUserIds = [])
    {
        $this->patientUserIds = $patientUserIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SchedulerService $service)
    {
        $service->removeScheduledCallsForWithdrawnAndPausedPatients($this->patientUserIds);
    }
}
