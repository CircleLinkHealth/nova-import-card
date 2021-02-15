<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ChangeBatchStatus implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const CACHE_KEY_PREFIX = 'reprocess_dispatched:';

    protected int    $batchId;
    protected string $status;

    /**
     * ChangeBatchStatus constructor.
     *
     * @param int $id
     */
    public function __construct(int $batchId, string $status)
    {
        $this->batchId = $batchId;
        $this->status  = $status;
    }

    public function handle()
    {
        if ( ! in_array($this->status, EligibilityBatch::STATUSES)) {
            return;
        }

        if ((int) $this->status === EligibilityBatch::STATUSES['complete'] && $this->stillHasPendingElibilityPatients($this->batchId)) {
            $cacheKey = self::CACHE_KEY_PREFIX.$this->batchId;
            $cache = Cache::driver(isProductionEnv() ? 'dynamodb' : config('cache.default'));
            if ( ! $cache->has($cacheKey)) {
                ProcessEligibilityBatch::dispatch($this->batchId);
                ProcessEligibilityBatch::dispatch($this->batchId)->delay(now()->addMinutes(3));
                ProcessEligibilityBatch::dispatch($this->batchId)->delay(now()->addMinutes(7));
                $cache->put($cacheKey, now()->timestamp, now()->addMinutes(10));
            }

            return;
        }

        EligibilityBatch::whereId($this->batchId)
            ->update([
                'status' => $this->status,
            ]);
    }

    private function hasPendingEligibilityJobs(int $batchId)
    {
        return EligibilityJob::pendingProcessing()
            ->where('batch_id', $batchId)
            ->exists();
    }

    private function hasPendingTargetPatients(int $batchId)
    {
        return TargetPatient::where('status', '=', TargetPatient::STATUS_TO_PROCESS)
            ->where('batch_id', $batchId)
            ->exists();
    }

    private function stillHasPendingElibilityPatients(int $batchId)
    {
        if ($this->hasPendingTargetPatients($batchId)) {
            return true;
        }

        if ($this->hasPendingEligibilityJobs($batchId)) {
            return true;
        }

        return false;
    }
}
