<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Services\EligibilityChecker;
use CircleLinkHealth\Customer\Entities\Practice;
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
     * @var EligibilityJob
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
     *
     * @param EligibilityJob                               $eligibilityJob
     * @param EligibilityBatch                             $batch
     * @param \CircleLinkHealth\Customer\Entities\Practice $practice
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
        //Only process if EligibilityJob status is 0 (not_started)
        if (0 == $this->eligibilityJob->status) {
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
