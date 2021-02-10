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

class MarkBatchAsReadyToStart implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $batchId;

    /**
     * MarkBatchAsReadyToStart constructor.
     *
     * @param int $id
     */
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle()
    {
        EligibilityBatch::whereId($this->batchId)
            ->update([
                'status' => EligibilityBatch::STATUSES['not_started'],
            ]);
    }
}
