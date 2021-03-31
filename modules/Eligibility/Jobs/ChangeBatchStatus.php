<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;
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
    const CACHE_KEY_PREFIX              = 'reprocess_dispatched:';
    const MAX_DAILY_REDISPATCH_ATTEMPTS = 10;

    protected int    $batchId;
    protected string $status;
    protected int    $practiceId;
    
    /**
     * ChangeBatchStatus constructor.
     *
     * @param int $id
     */
    public function __construct(int $batchId, int $practiceId, string $status)
    {
        $this->batchId = $batchId;
        $this->status  = $status;
        $this->practiceId = $practiceId;
    }

    public function handle()
    {
        if ( ! in_array($this->status, EligibilityBatch::STATUSES)) {
            return;
        }

        if ((int) $this->status === EligibilityBatch::STATUSES['complete'] && $this->stillHasPendingElibilityPatients($this->batchId)) {
            $cacheKey = self::CACHE_KEY_PREFIX.$this->batchId;
            $cache    = Cache::driver(isProductionEnv() ? 'dynamodb' : config('cache.default'));

            if ($cache->has($cacheKey)) {
                $attemptCount = (int) $cache->get($cacheKey);
                if ($attemptCount <= self::MAX_DAILY_REDISPATCH_ATTEMPTS) {
                    ProcessEligibilityBatch::dispatch($this->batchId);
                    $cache->put($cacheKey, ++$attemptCount, now()->endOfDay());
                }

                return;
            }

            $attemptCount = 0;
            $cache->put($cacheKey, $attemptCount, now()->endOfDay());
            ProcessEligibilityBatch::dispatch($this->batchId);

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

    private function hasPendingPracticePullDemographics(int $batchId)
    {
        return Demographics::where('practice_id', $this->practiceId)->whereNull('eligibility_job_id')
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

        if ($this->hasPendingPracticePullDemographics($batchId)) {
            return true;
        }

        return false;
    }
}
