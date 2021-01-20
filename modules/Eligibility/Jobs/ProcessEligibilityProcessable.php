<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Contracts\EligibilityProcessable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEligibilityProcessable implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var \CircleLinkHealth\Eligibility\Contracts\EligibilityProcessable
     */
    private $processable;

    /**
     * Create a new job instance.
     */
    public function __construct(EligibilityProcessable $processable)
    {
        $this->processable = $processable;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $list = $this->processable->processEligibility();
    }
}
