<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tylercd100\Notify\Facades\Slack;

class SendSlackMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $channel;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($channel, $message)
    {
        $this->channel = $channel;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Slack::to($this->channel)->send($this->message);
    }
}
