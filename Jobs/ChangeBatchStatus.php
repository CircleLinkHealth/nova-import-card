<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeBatchStatus implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        if (in_array($this->status, EligibilityBatch::STATUSES)) {
            EligibilityBatch::whereId($this->batchId)
                ->update([
                    'status' => $this->status,
                ]);
        }
    }
}
