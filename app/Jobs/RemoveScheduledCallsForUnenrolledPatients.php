<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Services\Calls\SchedulerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveScheduledCallsForUnenrolledPatients implements ShouldQueue
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
     *
     * @return void
     */
    public function __construct(int ...$patientUserIds)
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
