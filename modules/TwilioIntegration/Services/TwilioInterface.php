<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Services;

use NotificationChannels\Twilio\TwilioMessage;

interface TwilioInterface
{
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
