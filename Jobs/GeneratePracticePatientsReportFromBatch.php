<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Invoices\GeneratePracticePatientsReport;
use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePracticePatientsReportFromBatch implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $batchId;

    private Carbon $date;

    private int $practiceId;

    public function __construct(int $practiceId, string $date, string $batchId)
    {
        $this->practiceId = $practiceId;
        $this->date       = Carbon::parse($date);
        $this->batchId    = $batchId;
    }

    public function handle()
    {
        $batchRepo           = app(BatchableStoreRepository::class);
        $batch               = $batchRepo->get($this->batchId);
        $practicePatientData = collect($batch)
            ->filter(fn ($item) => $item['practice_id'] === $this->practiceId)
            ->map(fn ($item)    => $item['data'])
            ->flatten(1)
            ->toArray();

        $media = (new GeneratePracticePatientsReport())
            ->setDate($this->date)
            ->setPracticeId($this->practiceId)
            ->setPatientsData($practicePatientData)
            ->execute();
        $batchRepo->store($this->batchId, $this->practiceId, BatchableStoreRepository::MEDIA_TYPE, $media->id);
    }

    public function tags(): array
    {
        return [
            'GeneratePracticePatientsReportFromBatch',
            'practiceId:'.$this->practiceId,
            'date:'.$this->date,
            'batchId:'.$this->batchId,
        ];
    }
}
