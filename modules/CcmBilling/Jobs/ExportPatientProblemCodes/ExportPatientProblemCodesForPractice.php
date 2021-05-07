<?php

namespace CircleLinkHealth\CcmBilling\Jobs\ExportPatientProblemCodes;

use CircleLinkHealth\CcmBilling\Jobs\ChunksEloquentBuilderJob;
use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */
class ExportPatientProblemCodesForPractice extends ChunksEloquentBuilderJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $practiceId;
    protected string $batchId;

    public function __construct(int $practiceId, string $batchId)
    {
        $this->practiceId = $practiceId;
        $this->batchId    = $batchId;
    }

    public function getBuilder(): Builder
    {
        return User::select(['id', 'display_name'])
                   ->ofPractice($this->practiceId)
                   ->ofType('participant')
                   ->whereHas('patientInfo', fn($q) => $q->enrolled())
                   ->with([
                       'ccdProblems'     => fn($q) => $q->forBilling(),
                       'primaryPractice' => fn($q) => $q->select(['id', 'display_name']),
                   ])
                   ->offset($this->getOffset())
                   ->limit($this->getLimit());
    }

    public function handle()
    {
        $offset = $this->getOffset();
        $limit  = $this->getLimit();
        $total  = $this->getTotal();
        Log::info("Patient Problem Codes Report: Creating Patient Problem Codes report for practice[{$this->practiceId}] and batch[{$this->batchId}]. Total[$total] | Offset[$offset] | Limit[$limit] | Chunk[$this->chunkId]");
        $data = $this->getBuilder()
                     ->get()
                     ->map(function (User $user) {
                         return [
                             $user->getPrimaryPracticeName(),
                             $user->id,
                             $user->display_name,
                             $this->formatProblemCodesForReport($user->ccdProblems),
                         ];
                     });

        $this->storeIntoCache($data);

        Log::info("Patient Problem Codes Report: Creating Patient Problem Codes report for practice[{$this->practiceId}] and batch[{$this->batchId}]. Total[$total] | Offset[$offset] | Limit[$limit] | Chunk[$this->chunkId]");
    }

    private function storeIntoCache(Collection $data): void
    {
        app(BatchableStoreRepository::class)->store($this->batchId, BatchableStoreRepository::JSON_TYPE,
            $data->toArray(), $this->chunkId);
    }

    private function formatProblemCodesForReport(Collection $problems): string
    {
        return $problems->isNotEmpty()
            ?
            $problems->map(
                function (Problem $problem) {
                    return $problem->icd10Code();
                }
            )->filter()
                     ->unique()
                     ->implode(', ')
            : 'N/A';
    }
}