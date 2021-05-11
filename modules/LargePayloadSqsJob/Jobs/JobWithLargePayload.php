<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsJob\Jobs;

use CircleLinkHealth\LargePayloadSqsJob\Traits\LargePayloadS3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class JobWithLargePayload implements ShouldQueue, ShouldBeEncrypted
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
        Log::debug("Running Job JobWithLargePayload {$this->bucket}:{$this->key}");

        $message = json_decode($this->getS3Client()->get($this->key), true);
    
        $command = $message['data']['command'] ?? [];
        
        if ((bool) base64_decode($command)) {
            $job = app(Encrypter::class)->decrypt($command);
            
            if (is_string($job)) {
                $job = unserialize($job);
            }
        } else {
            $job = unserialize($command);
        }

        app(Dispatcher::class)->dispatchNow($job);
    }
}
