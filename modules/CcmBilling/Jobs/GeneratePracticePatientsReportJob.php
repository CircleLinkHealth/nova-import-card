<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovableBillingStatusesQuery;
use CircleLinkHealth\CcmBilling\Domain\Invoices\GeneratePracticePatientReport;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GeneratePracticePatientsReportJob extends ChunksEloquentBuilderJob
{
    use ApprovableBillingStatusesQuery;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $batchId;

    public Carbon $date;

    public int $practiceId;

    private array $locationIds;

    public function __construct(int $practiceId, array $locationIds, string $date, string $batchId)
    {
        $this->date        = Carbon::parse($date);
        $this->practiceId  = $practiceId;
        $this->locationIds = $locationIds;
        $this->batchId     = $batchId;
    }

    public function getBuilder(): Builder
    {
        return $this->approvedBillingStatusesQuery($this->locationIds, $this->date, true)
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
        $offset = $this->getOffset();
        $limit = $this->getLimit();
        $total = $this->getTotal();
        Log::info("Invoices: Creating Practice Patient Report data for practice: {$this->practiceId}, for batch: {$this->batchId}. Total[$total] | Offset[$offset] | Limit[$limit]");
        $data = $this->getBuilder()
            ->get()
            ->map(fn (PatientMonthlyBillingStatus $billingStatus) => (new GeneratePracticePatientReport())->setBillingStatus($billingStatus)->execute()->toCsvRow());

        $this->storeIntoCache($data);

        $dataCount = $data->count();
        Log::channel('database')->debug("Batch[$this->batchId]|Practice[$this->practiceId]|Total[$total]|Offset[$offset]|Limit[$limit]|DataCount[$dataCount]");

        Log::info("Invoices: Ending creation of Practice Patient Report data for practice: {$this->practiceId}, for batch: {$this->batchId}. Total[$total] | Offset[$offset] | Limit[$limit]");
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
