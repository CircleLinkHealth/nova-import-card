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

class SendSlackMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
        \Slack::to($this->to)->send($this->message);
    }
}
