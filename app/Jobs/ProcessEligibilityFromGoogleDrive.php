<?php

namespace App\Jobs;

use App\EligibilityBatch;
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
     * @var EligibilityBatch
     */
    private $batch;

    /**
     * Create a new job instance.
     *
     * @param EligibilityBatch $batch
     */
    public function __construct(EligibilityBatch $batch)
    {
        $this->dir                 = $batch->options['dir'];
        $this->practiceName        = $batch->options['practiceName'];
        $this->filterLastEncounter = (boolean)$batch->options['filterLastEncounter'];
        $this->filterInsurance     = (boolean)$batch->options['filterInsurance'];
        $this->filterProblems      = (boolean)$batch->options['filterProblems'];
        $this->batch               = $batch;
    }

    /**
     * Execute the job.
     *
     * @param ProcessEligibilityService $processEligibilityService
     *
     * @return void
     */
    public function handle(ProcessEligibilityService $processEligibilityService)
    {
        $processEligibilityService
            ->fromGoogleDrive($this->batch);
    }
}
