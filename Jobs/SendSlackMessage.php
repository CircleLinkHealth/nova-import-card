<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Traits\ScoutMonitoredDispatchable as Dispatchable;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSlackMessage implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected $message;

    protected $to;

    /**
     * Create a new job instance.
     *
     * @param mixed $to
     * @param mixed $message
     */
    public function __construct($to, $message)
    {
        $this->to      = $to;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            \Slack::to($this->to)->send($this->message);
        } catch (ClientException $e) {
            Log::error($e->getMessage()." `To:{$this->to}` `Message:{$this->message}`");
        }
    }
}
