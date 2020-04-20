<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEligibilityFromGoogleDrive implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityBatch
     */
    private $batch;
    private $dir;
    private $filterInsurance;
    private $filterLastEncounter;
    private $filterProblems;
    private $practiceName;

    /**
     * Create a new job instance.
     */
    public function __construct(EligibilityBatch $batch)
    {
        $this->dir                 = $batch->options['dir'];
        $this->practiceName        = $batch->options['practiceName'];
        $this->filterLastEncounter = (bool) $batch->options['filterLastEncounter'];
        $this->filterInsurance     = (bool) $batch->options['filterInsurance'];
        $this->filterProblems      = (bool) $batch->options['filterProblems'];
        $this->batch               = $batch;
    }

    /**
     * Execute the job.
     */
    public function handle(ProcessEligibilityService $processEligibilityService)
    {
        $processEligibilityService
            ->fromGoogleDrive($this->batch);
    }
}
