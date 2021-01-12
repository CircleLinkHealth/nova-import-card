<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\SharedModels\Entities\OutgoingSms;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Twilio\Twilio;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Propaganistas\LaravelPhone\PhoneNumber as PhoneNumberValidator;

class OutgoingSmsObserver
{
    public function created(OutgoingSms $outgoingSms)
    {
        $shouldSend  = true;
        $phoneNumber = $outgoingSms->receiver_phone_number;
        if (isProductionEnv()) {
            $phoneNumber = formatPhoneNumberE164($outgoingSms->receiver_phone_number);
            $shouldSend  = PhoneNumberValidator::make($phoneNumber, 'US')->isOfType('mobile');
        }

        if ( ! $shouldSend) {
            $outgoingSms->status         = 'failed';
            $outgoingSms->status_details = 'invalid US number or not a US mobile number';
        } else {
            $message                       = (new TwilioSmsMessage())->content($outgoingSms->message);
            $message->statusCallback       = route('twilio.sms.status');
            $message->statusCallbackMethod = 'POST';

            try {
                $res                      = app(Twilio::class)->sendMessage($message, $phoneNumber);
                $outgoingSms->sid         = $res->sid;
                $outgoingSms->account_sid = $res->accountSid;
            } catch (\Exception $exception) {
                $outgoingSms->status         = 'failed';
                $outgoingSms->status_details = $exception->getMessage();
                Log::error($exception->getMessage());
            }
        }

        $outgoingSms->save();
    }

    public function creating(OutgoingSms $outgoingSms)
    {
        $outgoingSms->sender_user_id = auth()->id();
        if (isProductionEnv()) {
            $outgoingSms->receiver_phone_number = formatPhoneNumberE164($outgoingSms->receiver_phone_number);
        }
    }
}
