<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Tests\Concerns\TwilioFake;

use CircleLinkHealth\TwilioIntegration\Services\TwilioInterface;

trait WithTwilioMock
{
    /**
     * Mocked Twilio implementation.
     *
     * @var TwilioInterface
     */
    private $twilio;

    private function twilio(): TwilioInterface
    {
        if ( ! $this->twilio) {
            $this->twilio = Twilio::fake();
        }

        return $this->twilio;
    }
}
