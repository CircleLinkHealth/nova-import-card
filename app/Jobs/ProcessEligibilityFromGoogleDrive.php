<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EligibilityBatch;
use App\Services\CCD\ProcessEligibilityService;
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
     * @var EligibilityBatch
     */
    private $batch;
    private $dir;
    private $filterInsurance;
    private $filterLastEncounter;
    private $filterProblems;
    private $practiceName;

    /**
     * Create a new job instance.
     *
     * @param EligibilityBatch $batch
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
     *
     * @param ProcessEligibilityService $processEligibilityService
     */
    public function handle(ProcessEligibilityService $processEligibilityService)
    {
        $processEligibilityService
            ->fromGoogleDrive($this->batch);
    }
}
