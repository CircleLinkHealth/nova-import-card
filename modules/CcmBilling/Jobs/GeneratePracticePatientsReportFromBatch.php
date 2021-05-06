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
use Illuminate\Support\Facades\Log;

class GeneratePracticePatientsReportFromBatch implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $batchId;

    private array $chunks;

    private Carbon $date;

    private int $practiceId;

    public function __construct(int $practiceId, string $date, string $batchId, array $chunks)
    {
        $this->practiceId = $practiceId;
        $this->date       = Carbon::parse($date);
        $this->batchId    = $batchId;
        $this->chunks     = $chunks;
    }

    public function handle()
    {
        Log::info("Invoices: Creating Practice Patient Report from batch for practice[{$this->practiceId}] and batch[{$this->batchId}]");
        $batchRepo           = app(BatchableStoreRepository::class);
        $batch               = collect($batchRepo->get($this->batchId, BatchableStoreRepository::JSON_TYPE, $this->chunks));
        $practicePatientData = $batch
            ->map(fn ($item) => $item['data'])
            ->flatten(1)
            ->toArray();

        $media = (new GeneratePracticePatientsReport())
            ->setDate($this->date)
            ->setPracticeId($this->practiceId)
            ->setPatientsData($practicePatientData)
            ->execute();
        $batchRepo->store($this->batchId, BatchableStoreRepository::MEDIA_TYPE, $media->id, $this->practiceId);

        $batchItemsCount = $batch->count();
        $patientsCount   = count($practicePatientData);
        Log::channel('database')->debug("Batch[$this->batchId] | Practice[$this->practiceId] | BatchItemsCount[$batchItemsCount] | PatientsCount[$patientsCount] | Media[$media->id]");

        Log::info("Invoices: Ending creation Practice Patient Report from batch for practice: {$this->practiceId}, for batch: {$this->batchId}");
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
