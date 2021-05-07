<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */
namespace CircleLinkHealth\CcmBilling\Jobs\ExportPatientProblemCodes;

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

class ExportPatientProblemCodesForPracticeFromBatch implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $batchId;

    private array $chunks;

    private int $practiceId;

    public function __construct(int $practiceId, string $batchId, array $chunks)
    {
        $this->practiceId = $practiceId;
        $this->batchId    = $batchId;
        $this->chunks     = $chunks;
    }

    public function handle()
    {
        Log::info("Patient Problem Codes Report: Creating Patient Problem Codes report for practice[{$this->practiceId}] and batch[{$this->batchId}]");
        $batchRepo           = app(BatchableStoreRepository::class);
        $batch               = collect($batchRepo->get($this->batchId, BatchableStoreRepository::JSON_TYPE, $this->chunks));
        $practicePatientData = $batch
            ->map(fn ($item) => $item['data'])
            ->flatten(1)
            ->toArray();

//        $media = (new GeneratePracticePatientsReport())
//            ->setDate($this->date)
//            ->setPracticeId($this->practiceId)
//            ->setPatientsData($practicePatientData)
//            ->execute();

//        $batchRepo->store($this->batchId, BatchableStoreRepository::MEDIA_TYPE, $media->id, $this->practiceId);

        Log::info("Patient Problem Codes Report: Ending creation of Patient Problem Codes report for practice: {$this->practiceId}, for batch: {$this->batchId}");
    }

    public function tags(): array
    {
        return [
            'ExportPatientProblemCodesForPracticeFromBatch',
            'practiceId:'.$this->practiceId,
            'batchId:'.$this->batchId,
        ];
    }
}