<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Services;

use NotificationChannels\Twilio\Twilio;

/**
 * The purpose of this class is to introduce implementing TwilioInterface, so that we can easily mock Twilio SDK.
 * We are resolving TwilioInterface to this class.
 *
 * Class CustomTwilioService
 */
class CustomTwilioService extends Twilio implements TwilioInterface
{
}
