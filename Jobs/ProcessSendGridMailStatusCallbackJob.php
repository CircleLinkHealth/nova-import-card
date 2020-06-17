<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Entities\SendGridRawLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
     * @param mixed $input
     *
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
        $this->storeRawLogs();

        foreach ($this->input as $event) {
            if (isset($event['smtp-id'])) {
                $smtpId = $event['smtp-id'];
                if (Str::startsWith($smtpId, ['<'])) {
                    $smtpId = Str::substr($smtpId, 1);
                    $smtpId = Str::substr($smtpId, 0, Str::length($smtpId) - 1);
                }
                //DELIVERABILITY DATA has smtp-id
                SendGridNotificationStatusUpdateJob::dispatch(
                    $smtpId,
                    false,
                    [
                        'sg_event_id'   => $event['sg_event_id'] ?? null,
                        'sg_message_id' => $event['sg_message_id'] ?? null,
                        'value'         => $event['event'],
                        'details'       => $event['timestamp'],
                        'email'         => $event['email'],
                    ],
                );
            } elseif (isset($event['sg_message_id'])) {
                //ENGAGEMENT DATA has sg_message_id
                SendGridNotificationStatusUpdateJob::dispatch(
                    $event['sg_message_id'],
                    true,
                    [
                        'value'   => $event['event'],
                        'details' => $event['timestamp'],
                        'email'   => $event['email'],
                    ],
                );
            } else {
                $ev = json_encode($event);
                Log::debug("could not process sendgrid event: $ev");
            }
        }
    }

    private function storeRawLogs(): void
    {
        try {
            SendGridRawLog::create([
                'events' => json_encode($this->input),
            ]);
        } catch (\Throwable $e) {
            Log::warning($e->getMessage());
        }
    }
}
