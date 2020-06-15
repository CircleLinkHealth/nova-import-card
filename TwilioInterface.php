<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core;

use NotificationChannels\Twilio\TwilioMessage;

interface TwilioInterface
{
    /**
     * Send a TwilioMessage to the a phone number.
     *
     * @param string $to
     * @param bool   $useAlphanumericSender
     *
     * @throws \Twilio\Exceptions\TwilioException
     *
     * @return mixed
     */
    public function sendMessage(TwilioMessage $message, $to, $useAlphanumericSender = false);
}
