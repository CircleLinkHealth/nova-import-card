<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticePatientsReportFromBatch;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticePatientsReportJob;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticesQuickbooksReportJob;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Jobs\ChainableJob;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Support\Str;

class PracticesInvoicesService
{
    private PracticeProcessorRepository $repository;

    public function __construct(PracticeProcessorRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate(array $practices, Carbon $date, string $format, int $requestedByUserId): void
    {
        $patientsToProcessPerJob = AppConfig::pull('practice_patient_report_process_per_job', 100);
        $batchId                 = 'practices_invoices'.((string) Str::orderedUuid());

        $jobs = [];

        foreach ($practices as $practiceId) {
            $chunkedJobs = $this->repository
                ->approvedBillingStatuses($practiceId, $date)
                ->chunkIntoJobsAndGetArray($patientsToProcessPerJob, new GeneratePracticePatientsReportJob($practiceId, $date, $batchId));

            $jobs   = array_merge($jobs, $chunkedJobs);
            $jobs[] = new GeneratePracticePatientsReportFromBatch($practiceId, $date, $batchId);
        }

        $jobs[] = new GeneratePracticesQuickbooksReportJob($practices, $date, $batchId, $format, $requestedByUserId);

        ChainableJob::withChain($jobs)
            ->dispatch()
            ->allOnQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
    }
}
