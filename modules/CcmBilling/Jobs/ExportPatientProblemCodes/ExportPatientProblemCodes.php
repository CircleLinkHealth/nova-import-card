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

    public function __construct(array $practiceIds)
    {
        $this->practiceIds = $practiceIds;
    }

    public function handle()
    {
        $batchId                 = 'practices_invoices'.((string) Str::orderedUuid());

        $jobs = [];


        foreach ($this->practiceIds as $practiceId) {
            $chunkedJobs =  User::select(['id', 'display_name'])
                      ->ofPractice($practiceId)
                      ->ofType('participant')
                      ->whereHas('patientInfo', fn($q) => $q->enrolled())
                      ->chunkIntoJobsAndGetArray(100, new ExportPatientProblemCodesForPractice());

            $chunkIds = collect($chunkedJobs)
                ->map(fn (ExportPatientProblemCodesForPractice $chunkedJob) => $chunkedJob->getChunkId())
                ->toArray();

            $jobs   = array_merge($jobs, $chunkedJobs);
            $jobs[] = new ExportPatientProblemCodesForPracticeFromBatch();
        }

        $jobs[] = new GeneratePatientProblemCodesQuickbooksReportJob();

        Bus::chain($jobs)
            // ->onConnection('sync')
           ->onConnection('sqs-fifo')
           ->onQueue(getCpmQueueName(CpmConstants::FIFO_QUEUE))
           ->dispatch();

    }
}