<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\OutgoingSms;
use NotificationChannels\Twilio\Twilio;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Propaganistas\LaravelPhone\PhoneNumber as PhoneNumberValidator;

class OutgoingSmsObserver
{
    public function created(OutgoingSms $outgoingSms)
    {
        $phoneNumber = formatPhoneNumberE164($outgoingSms->receiver_phone_number);

        if (PhoneNumberValidator::make($phoneNumber, 'US')->isOfType('mobile')) {
            app(Twilio::class)->sendMessage((new TwilioSmsMessage())
                ->content($outgoingSms->message), $phoneNumber);
        }
    }

    public function creating(OutgoingSms $outgoingSms)
    {
        $outgoingSms->sender_user_id        = auth()->id();
        $outgoingSms->receiver_phone_number = formatPhoneNumberE164($outgoingSms->receiver_phone_number);
    }
}
