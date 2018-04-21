<?php

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\ProcessCcda;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
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
        $batches = EligibilityBatch::where('status', '<', 2)
                                   ->whereType(EligibilityBatch::TYPE_GOOGLE_DRIVE)
                                   ->get()
                                   ->map(function ($batch) {
                                       $result = $this->processEligibilityService->fromGoogleDrive($batch);

                                       if ($result) {
                                           $batch->status = EligibilityBatch::STATUSES['processing'];
                                           $batch->save();

                                           return $result;
                                       }

                                       $practice = Practice::whereName($batch->options['practiceName'])->firstOrFail();

                                       $unprocessed = Ccda::whereBatchId($batch->id)
                                                          ->whereStatus(Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)
                                                          ->inRandomOrder()
                                                          ->take(20)
                                                          ->get()
                                                          ->map(function ($ccda) use ($batch, $practice) {
                                                              ProcessCcda::withChain([
                                                                  new CheckCcdaEnrollmentEligibility($ccda->id,
                                                                      $practice, $batch),
                                                              ])->dispatch($ccda->id);

                                                              return $ccda;
                                                          });

                                       if ($unprocessed->isEmpty()) {
                                           $batch->status = EligibilityBatch::STATUSES['complete'];
                                           $batch->save();
                                       }
                                   });
    }
}
