<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\EligibilityChecker;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSinglePatientEligibility implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $eligibilityJobId;

    protected bool $force;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $eligibilityJobId,
        bool $force = false
    ) {
        $this->eligibilityJobId = $eligibilityJobId;
        $this->force            = $force;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $ej    = EligibilityJob::with('batch.practice')->findOrFail($this->eligibilityJobId);
        $batch = $ej->batch;

        if ($this->shouldProcess($ej)) {
            new EligibilityChecker(
                $ej,
                $batch->practice,
                $batch,
                $batch->shouldFilterLastEncounter(),
                $batch->shouldFilterInsurance(),
                $batch->shouldFilterProblems(),
                true
            );
        }
    }

    public function shouldProcess(EligibilityJob $ej)
    {
        if (true === $this->force) {
            return true;
        }

        if (0 == $ej->status) {
            return true;
        }

        return 1 == $ej->status && $ej->updated_at->lt(now()->subMinutes(10));
    }
}
