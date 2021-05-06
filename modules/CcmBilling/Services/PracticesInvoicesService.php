<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticePatientsReportFromBatch;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticePatientsReportJob;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticesQuickbooksReportJob;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class PracticesInvoicesService
{
    private LocationProcessorRepository $repository;

    public function __construct(LocationProcessorRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate(array $practices, Carbon $date, string $format, int $requestedByUserId): void
    {
        $patientsToProcessPerJob = AppConfig::pull('practice_patient_report_process_per_job', 100);
        $batchId                 = 'practices_invoices'.((string) Str::orderedUuid());

        $jobs = [];

        $locations = $this->getLocations($practices);

        foreach ($practices as $practiceId) {
            $locationIds = $locations->where('practice_id', '=', $practiceId)
                ->pluck('id')
                ->toArray();
            $chunkedJobs = $this->repository
                ->approvedBillingStatuses($locationIds, $date)
                ->chunkIntoJobsAndGetArray($patientsToProcessPerJob, new GeneratePracticePatientsReportJob($practiceId, $locationIds, $date->toDateString(), $batchId));

            $chunkIds = collect($chunkedJobs)
                ->map(fn (GeneratePracticePatientsReportJob $chunkedJob) => $chunkedJob->getChunkId())
                ->toArray();

            $jobs   = array_merge($jobs, $chunkedJobs);
            $jobs[] = new GeneratePracticePatientsReportFromBatch($practiceId, $date->toDateString(), $batchId, $chunkIds);
        }

        $jobs[] = new GeneratePracticesQuickbooksReportJob($practices, $date->toDateString(), $format, $requestedByUserId, $batchId);

        Bus::chain($jobs)
            // ->onConnection('sync')
            ->onConnection('sqs-fifo')
            ->onQueue(getCpmQueueName(CpmConstants::FIFO_QUEUE))
            ->dispatch();
    }

    private function getLocations(array $practiceIds): Collection
    {
        return Location::whereIn('practice_id', $practiceIds)
            ->get(['id', 'practice_id']);
    }
}
