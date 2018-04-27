<?php

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\MakePhoenixHeartWelcomeCallList;
use App\Jobs\ProcessCcda;
use App\Jobs\ProcessSinglePatientEligibility;
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
        $singleFileCsvs = EligibilityBatch::where('status', '<', 2)
                                          ->whereType(EligibilityBatch::TYPE_ONE_CSV)
                                          ->with('practice')
                                          ->get()
                                          ->map(function ($batch) {
                                              $result = $this->processEligibilityService->processCsvForEligibility($batch);

                                              if ($result) {
                                                  $batch->status = EligibilityBatch::STATUSES['processing'];
                                                  $batch->save();

                                                  return $batch;
                                              }

                                              $unprocessed = EligibilityJob::whereBatchId($batch->id)
                                                                           ->where('status', '<=', 2)
                                                                           ->get();

                                              if ($unprocessed->isEmpty()) {
                                                  $batch->status = EligibilityBatch::STATUSES['complete'];
                                                  $batch->save();

                                                  return $batch;
                                              }

                                              $unprocessed->map(function ($job) use ($batch) {
                                                  ProcessSinglePatientEligibility::dispatch(
                                                      collect([$job->data]),
                                                      $job,
                                                      $batch,
                                                      $batch->practice
                                                  );
                                              });

                                              $batch->status = EligibilityBatch::STATUSES['processing'];
                                              $batch->save();

                                              return $batch;
                                          });

        $googleDriveCcds = EligibilityBatch::where('status', '<', 2)
                                           ->whereType(EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS)
                                           ->get()
                                           ->map(function ($batch) {
                                               $result = $this->processEligibilityService->fromGoogleDrive($batch);

                                               if ($result) {
                                                   $batch->status = EligibilityBatch::STATUSES['processing'];
                                                   $batch->save();

                                                   return $batch;
                                               }

                                               $practice = Practice::findOrFail($batch->practice_id);

                                               $unprocessed = Ccda::whereBatchId($batch->id)
                                                                  ->whereStatus(Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)
                                                                  ->inRandomOrder()
                                                                  ->take(10)
                                                                  ->get()
                                                                  ->map(function ($ccda) use ($batch, $practice) {
                                                                      ProcessCcda::withChain([
                                                                          (new CheckCcdaEnrollmentEligibility($ccda->id,
                                                                              $practice,
                                                                              $batch))->onQueue('ccda-processor'),
                                                                      ])->dispatch($ccda->id)
                                                                                 ->onQueue('ccda-processor');

                                                                      return $ccda;
                                                                  });

                                               if ($unprocessed->isEmpty()) {
                                                   $batch->status = EligibilityBatch::STATUSES['complete'];
                                                   $batch->save();

                                                   return $batch;
                                               }

                                               $batch->status = EligibilityBatch::STATUSES['processing'];
                                               $batch->save();

                                               return $batch;
                                           });

        $phx = EligibilityBatch::where('status', '<', 2)
                               ->whereType(EligibilityBatch::TYPE_PHX_DB_TABLES)
                               ->get()
                               ->map(function ($batch) {
                                   MakePhoenixHeartWelcomeCallList::dispatch($batch)->onQueue('ccda-processor');
                               });
    }
}
