<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Contracts\Services\TwilioClientable;
use App\OutgoingSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

    /** @var array */
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
        $messageSid = $this->input['MessageSid'];
        $accountSid = $this->input['AccountSid'];

        try {
            $twilio        = app(TwilioClientable::class);
            $client        = $twilio->getClient();
            $message       = $client->messages($messageSid)->fetch();
            $status        = $message->status;
            $statusDetails = $message->errorMessage;
        } catch (\Exception $e) {
            $status        = $this->input['MessageStatus'];
            $statusDetails = $this->input['ErrorCode'] ?? null;
            if ($statusDetails && isset(self::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails])) {
                $statusDetails = self::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails];
            }
        }

        //check if this message was initiated from SuperAdmin -> SMS (OutgoingSms Model)
        //if not, assume it's a notification
        $handled = $this->handleOutgoingSmsCallback($messageSid, $accountSid, $status, $statusDetails);
        if ( ! $handled) {
            $this->handleSmsNotificationCallback($messageSid, $accountSid, $status, $statusDetails);
        }
    }

    /**
     * Update the status of the sms.
     *
     * @return bool
     */
    private function handleOutgoingSmsCallback(string $sid, string $accountSid, string $status, string $statusDetails = null)
    {
        /** @var OutgoingSms $sms */
        $sms = OutgoingSms::where('sid', '=', $sid)
            ->where('account_sid', '=', $accountSid)
            ->first();
        if ($sms) {
            $sms->status         = $status;
            $sms->status_details = $statusDetails;
            $sms->save();

            return true;
        }

        return false;
    }

    private function handleSmsNotificationCallback(string $sid, string $accountSid, string $status, string $statusDetails = null)
    {
        TwilioNotificationStatusUpdateJob::dispatch(
            $sid,
            $accountSid,
            [
                'value'   => $status,
                'details' => $statusDetails,
            ],
        );
    }
}
