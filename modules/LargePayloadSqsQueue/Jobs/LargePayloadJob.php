<?php

namespace CircleLinkHealth\LargePayloadSqsQueue\Jobs;

use CircleLinkHealth\LargePayloadSqsQueue\Traits\LargePayloadS3Client;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LargePayloadJob implements ShouldQueue
{
    use Dispatchable, LargePayloadS3Client, InteractsWithQueue, Queueable, SerializesModels;
    
    protected string $key;
    protected string $bucket;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $key, string $bucket)
    {
        $this->key = $key;
        $this->bucket = $bucket;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(Dispatcher::class)->dispatchNow(unserialize($this->getS3Client()->get($this->key)));
    }
}
