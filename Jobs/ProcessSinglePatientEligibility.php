<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\EligibilityChecker;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSinglePatientEligibility implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var EligibilityBatch
     */
    private $batch;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityJob
     */
    private $eligibilityJob;

    /**
     * @var bool
     */
    private $filterInsurance;

    /**
     * @var bool
     */
    private $filterLastEncounter;

    /**
     * @var bool
     */
    private $filterProblems;

    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    private $practice;

    /**
     * Create a new job instance.
     */
    public function __construct(
        EligibilityJob $eligibilityJob,
        EligibilityBatch $batch,
        Practice $practice
    ) {
        $this->practice            = $practice;
        $this->batch               = $batch;
        $this->filterLastEncounter = $batch->shouldFilterLastEncounter();
        $this->filterProblems      = $batch->shouldFilterProblems();
        $this->filterInsurance     = $batch->shouldFilterInsurance();
        $this->eligibilityJob      = $eligibilityJob;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        //Only process if EligibilityJob status is 0 (not_started), or 1 (processing) and last update is more than 10 minutes ago
        if (0 == $this->eligibilityJob->status
            || (1 == $this->eligibilityJob->status && $this->eligibilityJob->updated_at->lt(now()->subMinutes(10)))
        ) {
            new EligibilityChecker(
                $this->eligibilityJob,
                $this->practice,
                $this->batch,
                $this->filterLastEncounter,
                $this->filterInsurance,
                $this->filterProblems,
                true
            );
        }
    }
}
