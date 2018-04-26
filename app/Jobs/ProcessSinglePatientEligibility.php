<?php

namespace App\Jobs;

use App\EligibilityBatch;
use App\Practice;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProcessSinglePatientEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Collection
     */
    private $patient;

    /**
     * @var Practice
     */
    private $practice;

    /**
     * @var EligibilityBatch
     */
    private $batch;

    /**
     * @var bool
     */
    private $filterLastEncounter;

    /**
     * @var bool
     */
    private $filterInsurance;

    /**
     * @var bool
     */
    private $filterProblems;

    /**
     * Create a new job instance.
     *
     * @param Collection $patient
     * @param Practice $practice
     * @param EligibilityBatch $batch
     */
    public function __construct(
        Collection $patient,
        Practice $practice,
        EligibilityBatch $batch
    ) {
        $this->patient             = $patient;
        $this->practice            = $practice;
        $this->batch               = $batch;
        $this->filterLastEncounter = (boolean)$batch->options['filterLastEncounter'];
        $this->filterInsurance     = (boolean)$batch->options['filterProblems'];
        $this->filterProblems      = (boolean)$batch->options['filterInsurance'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        new WelcomeCallListGenerator(
            $this->patient,
            $this->filterLastEncounter,
            $this->filterInsurance,
            $this->filterProblems,
            true,
            $this->practice,
            null,
            null,
            $this->batch
        );
    }
}
