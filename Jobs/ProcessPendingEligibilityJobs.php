<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Core\Jobs\ChunksEloquentBuilderJobV2;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use Illuminate\Database\Eloquent\Builder;

class ProcessPendingEligibilityJobs extends ChunksEloquentBuilderJobV2
{
    protected int $batchId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $batchId
    ) {
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->getBuilder()->eachById(function ($job) {
            ProcessSinglePatientEligibility::dispatch(
                $job->id
            )->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
        });
    }

    public function query(): Builder
    {
        return EligibilityJob::whereBatchId($this->batchId)
            ->pendingProcessing();
    }
}
