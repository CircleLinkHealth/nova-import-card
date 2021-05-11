<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsJob\Jobs;

use CircleLinkHealth\LargePayloadSqsJob\Traits\LargePayloadS3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeletePayloadFromS3 implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use LargePayloadS3Client;
    use Queueable;
    use SerializesModels;
    protected string $bucket;

    protected string $key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $key, string $bucket)
    {
        $this->key    = $key;
        $this->bucket = $bucket;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("Running Job DeletePayloadFromS3 {$this->bucket}:{$this->key}");

        $this->getS3Client()->delete($this->key);
    }
}
