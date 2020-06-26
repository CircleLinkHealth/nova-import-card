<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\Entities\PostmarkRawLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPostmarkMailStatusCallbackJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const RECORD_TYPE_MAP = [
        'Delivery'           => 'delivered',
        'Bounce'             => 'bounced',
        'SpamComplaint'      => 'spam',
        'Open'               => 'opened',
        'Click'              => 'clicked',
        'SubscriptionChange' => 'subscription_changed',
    ];

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
        if ( ! isset($this->input['Metadata']) || ! isset($this->input['Metadata']['smtp-id'])) {
            Log::warning('could not find smtp-id in metadata');

            return;
        }
        $smtpId = $this->input['Metadata']['smtp-id'];
        $status = isset(self::RECORD_TYPE_MAP[$this->input['RecordType']]) ? self::RECORD_TYPE_MAP[$this->input['RecordType']] : 'unknown';
        if ('opened' === $status) {
            //process only on first open
            if ( ! $this->input['FirstOpen']) {
                return;
            }
        }

        $email = $this->input['Email'] ?? ($this->input['Recipient'] ?? null);
        PostmarkNotificationStatusUpdateJob::dispatch(
            $smtpId,
            [
                'pm_message_id' => $this->input['MessageID'],
                'value'         => $status,
                'details'       => $this->input['Details'] ?? null,
                'email'         => $email,
            ],
        );
    }

    private function storeRawLogs(): void
    {
        try {
            PostmarkRawLog::create([
                'event' => json_encode($this->input),
            ]);
        } catch (\Throwable $e) {
            Log::warning($e->getMessage());
        }
    }
}
