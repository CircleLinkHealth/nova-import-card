<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\TwilioClientable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process Twilio Sms Status Callbacks.
 *
 * UPDATE: we cannot be sure that status callbacks will be processed in order
 *         from our server, so it's better to actually go back to twilio and fetch the
 *         status of the message.
 *         This would not have been necessary if the timestamp was included in the request.
 *
 * Class ProcessTwilioSmsStatusCallbackJob
 */
class ProcessTwilioSmsStatusCallbackJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const TWILIO_MESSAGE_DELIVERY_CODES = [
        '30001' => 'Queue overflow',
        '30002' => 'Account suspended',
        '30003' => 'Unreachable destination handset',
        '30004' => 'Message blocked',
        '30005' => 'Unknown destination handset',
        '30006' => 'Landline or unreachable carrier',
        '30007' => 'Carrier violation',
        '30008' => 'Unknown error',
    ];

    /** @var bool */
    private $fetchStatusFromTwilio;

    /** @var array */
    private $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $input, bool $fetchStatusFromTwilio = true)
    {
        $this->input                 = $input;
        $this->fetchStatusFromTwilio = $fetchStatusFromTwilio;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $messageSid = $this->input['MessageSid'];
        $accountSid = $this->input['AccountSid'];

        $status = null;
        if ($this->fetchStatusFromTwilio) {
            try {
                $twilio        = app(TwilioClientable::class);
                $client        = $twilio->getClient();
                $message       = $client->messages($messageSid)->fetch();
                $status        = $message->status;
                $statusDetails = $message->errorMessage;
            } catch (\Exception $e) {
                Log::warning($e->getMessage());
            }
        }
        if ( ! $status) {
            $status        = $this->input['MessageStatus'];
            $statusDetails = $this->input['ErrorCode'] ?? null;
            if ($statusDetails && isset(self::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails])) {
                $statusDetails = self::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails];
            }
        }

        TwilioNotificationStatusUpdateJob::dispatch(
            $messageSid,
            $accountSid,
            [
                'value'   => $status,
                'details' => $statusDetails,
            ],
        );
    }
}
