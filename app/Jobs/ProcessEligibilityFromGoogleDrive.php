<?php

namespace App\Jobs;

use App\Services\CCD\ProcessEligibilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessEligibilityFromGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $dir;
    private $practiceName;
    private $filterLastEncounter;
    private $filterInsurance;
    private $filterProblems;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems)
    {
        $this->dir                 = $dir;
        $this->practiceName        = $practiceName;
        $this->filterLastEncounter = $filterLastEncounter;
        $this->filterInsurance     = $filterInsurance;
        $this->filterProblems      = $filterProblems;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ProcessEligibilityService $processEligibilityService)
    {
        $processEligibilityService
            ->fromGoogleDrive($this->dir, $this->practiceName, $this->filterLastEncounter, $this->filterInsurance,
                $this->filterProblems);
    }
}
