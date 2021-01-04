<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;

use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMonthltyPatientTime implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    /**
     * @var int
     */
    protected $patientId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ActivityService $service)
    {
        $service->processMonthlyActivityTime([$this->patientId]);
    }
}
