<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Services;

use CircleLinkHealth\TwilioIntegration\Http\Requests\LookupResponse;
use NotificationChannels\Twilio\TwilioMessage;

interface TwilioInterface
{
    public function lookup(string $e164PhoneNumber): LookupResponse;

    /**
     * Send a TwilioMessage to the a phone number.
     *
     * @param string $to
     *
     * @throws \Twilio\Exceptions\TwilioException
     *
     * @return mixed
     */
    public function sendMessage(TwilioMessage $message, ?string $to, bool $useAlphanumericSender = false);
}
