<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Services\WelcomeCallListGenerator;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

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
     * @var Collection
     */
    private $patient;

    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    private $practice;

    /**
     * Create a new job instance.
     *
     * @param Collection                                   $patient
     * @param \CircleLinkHealth\Customer\Entities\Practice $practice
     * @param EligibilityBatch                             $batch
     * @param EligibilityJob                               $eligibilityJob
     */
    public function __construct(
        Collection $patient,
        EligibilityJob $eligibilityJob,
        EligibilityBatch $batch,
        Practice $practice
    ) {
        $this->patient             = $patient;
        $this->practice            = $practice;
        $this->batch               = $batch;
        $this->filterLastEncounter = (bool) $batch->options['filterLastEncounter'];
        $this->filterProblems      = (bool) $batch->options['filterProblems'];
        $this->filterInsurance     = (bool) $batch->options['filterInsurance'];
        $this->eligibilityJob      = $eligibilityJob;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //Only process if EligibilityJob status is 0 (not_started)
        if (0 == $this->eligibilityJob->status) {
            new WelcomeCallListGenerator(
                $this->patient,
                $this->practice,
                $this->filterLastEncounter,
                $this->filterInsurance,
                $this->filterProblems,
                true,
                null,
                null,
                $this->batch,
                $this->eligibilityJob
            );
        }
    }
}
