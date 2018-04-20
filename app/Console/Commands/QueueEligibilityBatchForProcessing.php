<?php

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\Services\CCD\ProcessEligibilityService;
use Illuminate\Console\Command;

class QueueEligibilityBatchForProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process an eligibility batch of CCDAs.';

    /**
     * @var ProcessEligibilityService
     */
    protected $processEligibilityService;

    /**
     * Create a new command instance.
     *
     * @param ProcessEligibilityService $processEligibilityService
     */
    public function __construct(ProcessEligibilityService $processEligibilityService)
    {
        parent::__construct();
        $this->processEligibilityService = $processEligibilityService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $batch = EligibilityBatch::where('status', '<', 2)
                                 ->whereType(EligibilityBatch::TYPE_GOOGLE_DRIVE)
                                 ->first();

        if ($batch) {
            $this->processEligibilityService->fromGoogleDrive($batch);
        }
    }
}
