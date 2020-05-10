<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class KeepOriginalUserId implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private $enrolleeId;

    /**
     * @var int
     */
    private $oldUserId;

    /**
     * Create a new job instance.
     *
     * @param $enrolleeId
     */
    public function __construct(int $oldUserId, $enrolleeId)
    {
        $this->oldUserId  = $oldUserId;
        $this->enrolleeId = $enrolleeId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    }
}
