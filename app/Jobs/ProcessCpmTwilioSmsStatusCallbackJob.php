<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use App\OutgoingSms;
use CircleLinkHealth\Core\TwilioClientable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCpmTwilioSmsStatusCallbackJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var array */
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
            if ($statusDetails && isset(ProcessTwilioSmsStatusCallbackJob::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails])) {
                $statusDetails = ProcessTwilioSmsStatusCallbackJob::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails];
            }
        }

        //check if this message was initiated from SuperAdmin -> SMS (OutgoingSms Model)
        //if not, assume it's a notification
        $handled = $this->handleOutgoingSmsCallback($messageSid, $accountSid, $status, $statusDetails);
        if ( ! $handled) {
            $this->input['MessageStatus'] = $status;
            $this->input['ErrorCode']     = $statusDetails;
            ProcessTwilioSmsStatusCallbackJob::dispatchNow($this->input, false);
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
}
