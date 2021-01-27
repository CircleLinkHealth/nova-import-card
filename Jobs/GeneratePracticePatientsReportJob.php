<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovedBillingStatusesQuery;
use CircleLinkHealth\CcmBilling\Domain\Invoices\GeneratePracticePatientReport;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GeneratePracticePatientsReportJob extends ChunksEloquentBuilderJob
{
    use ApprovedBillingStatusesQuery;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $batchId;

    public Carbon $date;

    public int $practiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $practiceId, string $date, string $batchId)
    {
        $this->date       = Carbon::parse($date);
        $this->practiceId = $practiceId;
        $this->batchId    = $batchId;
    }

    public function getBuilder(): Builder
    {
        return $this->approvedBillingStatusesQuery($this->practiceId, $this->date, true)
            ->offset($this->getOffset())
            ->limit($this->getLimit());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->getBuilder()
            ->get()
            ->map(fn (PatientMonthlyBillingStatus $billingStatus) => (new GeneratePracticePatientReport())->setBillingStatus($billingStatus)->execute()->toCsvRow());

        $this->storeIntoCache($data);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'GeneratePracticePatientsReportJob',
            'practiceId:'.$this->practiceId,
            'date:'.$this->date,
            'batchId:'.$this->batchId,
        ];
    }

    private function storeIntoCache(Collection $data)
    {
        app(BatchableStoreRepository::class)->store($this->batchId, $this->practiceId, BatchableStoreRepository::JSON_TYPE, $data->toArray());
    }
}
