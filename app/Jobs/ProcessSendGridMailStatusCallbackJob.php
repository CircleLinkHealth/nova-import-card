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

class ProcessSendGridMailStatusCallbackJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var array
     */
    private $input;

    /**
     * Create a new job instance.
     *
     * @param  mixed $input
     * @return void
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->input as $event) {
            SendGridNotificationStatusUpdateJob::dispatch(
                $event['smtp-id'],
                [
                    'value'   => $event['event'],
                    'details' => $event['timestamp'],
                    'email'   => $event['email'],
                ],
            );
        }
    }
}
