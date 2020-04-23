<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Contracts\EligibilityProcessable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEligibilityProcessable implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var EligibilityProcessable
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
