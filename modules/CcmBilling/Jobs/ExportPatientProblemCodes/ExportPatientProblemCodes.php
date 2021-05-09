<?php

namespace CircleLinkHealth\CcmBilling\Jobs\ExportPatientProblemCodes;

use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticePatientsReportFromBatch;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticePatientsReportJob;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticesQuickbooksReportJob;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

class ExportPatientProblemCodes implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $practiceIds;
    protected int $requestorId;

    public function __construct(array $practiceIds, int $requestorId)
    {
        $this->practiceIds = $practiceIds;
        $this->requestorId = $requestorId;
    }

    public function handle()
    {
        $batchId                 = 'practices_invoices'.((string) Str::orderedUuid());
        $jobs = [];
        $chunkIds = [];

        foreach ($this->practiceIds as $practiceId) {
            $chunkedJobs =  User::select(['id', 'display_name'])
                      ->ofPractice($practiceId)
                      ->ofType('participant')
                      ->whereHas('patientInfo', fn($q) => $q->enrolled())
                      ->chunkIntoJobsAndGetArray(100, new ExportPatientProblemCodesForPractice($practiceId, $batchId));

            $chunkIds = array_merge(
                $chunkIds,
                collect($chunkedJobs)
                ->map(fn (ExportPatientProblemCodesForPractice $chunkedJob) => $chunkedJob->getChunkId())
                ->toArray()
            );

            $jobs   = array_merge($jobs, $chunkedJobs);
        }

        $jobs[] = new GeneratePatientProblemCodesQuickbooksReportJob($this->requestorId, $batchId, $chunkIds);

        Bus::chain($jobs)
           ->onConnection('sqs-fifo')
           ->onQueue(getCpmQueueName(CpmConstants::FIFO_QUEUE))
           ->dispatch();

    }
}