<?php

namespace App\Jobs;

use App\Practice;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class ProcessSinglePatientEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $patient;
    private $practice;
    private $filterLastEncounter;
    private $filterInsurance;
    private $filterProblems;

    /**
     * Create a new job instance.
     *
     * @param Collection $patient
     * @param Practice $practice
     * @param bool $filterLastEncounter
     * @param bool $filterInsurance
     * @param bool $filterProblems
     */
    public function __construct(
        Collection $patient,
        Practice $practice,
        bool $filterLastEncounter,
        bool $filterInsurance,
        bool $filterProblems
    ) {
        //
        $this->patient             = $patient;
        $this->practice            = $practice;
        $this->filterLastEncounter = $filterLastEncounter;
        $this->filterInsurance     = $filterInsurance;
        $this->filterProblems      = $filterProblems;
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
            $this->practice
        );
    }
}
