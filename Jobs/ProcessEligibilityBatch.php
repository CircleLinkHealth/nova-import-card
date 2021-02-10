<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Eligibility\Jobs\Athena\ProcessTargetPatientsForEligibilityInBatches;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class ProcessEligibilityBatch implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    protected $batch;

    /**
     * @var \CircleLinkHealth\Eligibility\ProcessEligibilityService
     */
    private $processEligibilityService;

    /**
     * Create a new job instance.
     */
    public function __construct(EligibilityBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Execute the job.
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->batch->type) {
            case EligibilityBatch::TYPE_ONE_CSV:
                $this->batch = $this->queueSingleCsvJobs($this->batch);
                break;
            case EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS:
                $this->batch = $this->queueGoogleDriveJobs($this->batch);
                break;
            case EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE:
                $this->batch = $this->queueClhMedicalRecordTemplateJobs($this->batch);
                break;
            case EligibilityBatch::ATHENA_API:
                $this->batch = $this->queueAthenaJobs($this->batch);
                break;
            case EligibilityBatch::RUNNING:
                $this->batch = $this->queueSingleEligibilityJobs($this->batch);
                break;
        }
    }

    /**
     * @throws \Exception
     *
     * @return EligibilityBatch|null
     */
    private function createEligibilityJobsFromJsonFile(EligibilityBatch $batch)
    {
    }

    private function queueAthenaJobs(EligibilityBatch $batch): EligibilityBatch
    {
        Bus::dispatchChain([
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['not_started'])],
            (new ProcessTargetPatientsForEligibilityInBatches($batch->id))
                ->splitToBatches(),
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['complete'])],
        ])->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));

        return $batch;
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function queueClhMedicalRecordTemplateJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $jobs = [];
        if ( ! array_key_exists('finishedReadingFile', $batch->options)) {
            $options                        = $batch->options;
            $options['finishedReadingFile'] = false;
            $batch->options                 = $options;
        }

        if ( ! (bool) $batch->options['finishedReadingFile']) {
            $jobs[] = new CreateEligibilityJobsFromCLHMedicalRecordJson($batch->id);
        }

        Bus::dispatchChain(array_merge(
            $jobs,
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['not_started'])],
            $batch->orchestratePendingJobsProcessing(50),
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['complete'])],
        ));

        return $batch;
    }

    private function queueGoogleDriveJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $jobs = [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['not_started'])];

        if ( ! $batch->isFinishedFetchingFiles()) {
            $jobs[] = new ProcessEligibilityFromGoogleDrive($batch->id);
        }

        Bus::dispatchChain(array_merge(
            $jobs,
            $batch->orchestratePendingJobsProcessing(50),
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['complete'])],
        ))->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));

        return $batch;
    }

    private function queueSingleCsvJobs(EligibilityBatch $batch): EligibilityBatch
    {
        $jobs = [];
        if (array_keys_exist(
            ['folder', 'fileName'],
            $batch->options
        ) && true !== (bool) $batch->options['finishedReadingFile']) {
            $jobs[] = new ProcessGoogleDriveCsv($batch->id);
        }

        Bus::dispatchChain(array_merge(
            $jobs,
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['not_started'])],
            $batch->orchestratePendingJobsProcessing(50),
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['complete'])],
        ));

        return $batch;
    }

    private function queueSingleEligibilityJobs(EligibilityBatch $batch)
    {
        Bus::dispatchChain(array_merge(
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['not_started'])],
            $batch->orchestratePendingJobsProcessing(50),
            [new ChangeBatchStatus($batch->id, EligibilityBatch::STATUSES['complete'])],
        ));
        
        return $batch;
    }
}
