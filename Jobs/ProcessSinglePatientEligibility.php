<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\EligibilityChecker;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
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
    
    /**
     * Create a new job instance.
     *
     * @param int $eligibilityJobId
     */
    public function __construct(
        int $eligibilityJobId
    ) {
        $this->eligibilityJobId = $eligibilityJobId;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $ej = EligibilityJob::with('batch.practice')->findOrFail($this->eligibilityJobId);
        $batch = $ej->batch;
        
        //Only process if EligibilityJob status is 0 (not_started), or 1 (processing) and last update is more than 10 minutes ago
        if (0 == $ej->status
            || (1 == $ej->status && $ej->updated_at->lt(now()->subMinutes(10)))
        ) {
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
}
