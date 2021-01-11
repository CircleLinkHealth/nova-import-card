<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\TwilioFake;

use CircleLinkHealth\Core\TwilioInterface;
use NotificationChannels\Twilio\TwilioMessage;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Log\LoggerInterface;

class TwilioFake implements TwilioInterface
{
    protected $calls = [];
    protected $connection;
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = new TwilioFakeLogDriver($logger);
    }

    /**
     * @param string      $to
     * @param string      $message
     * @param string|null $connection
     *
     * @return $this
     */
    public function assertMessageNotSent($to, $message)
    {
        PHPUnit::assertFalse(
            1 === $this->sent($this->logger->getMessages(), $to, $message)->count(),
            "The expected [{$message}] message was not sent."
        );

        return $this;
    }

    /**
     * @param string      $to
     * @param string      $message
     * @param string|null $connection
     *
     * @return $this
     */
    public function assertMessageSent($to, $message)
    {
        PHPUnit::assertTrue(
            1 === $this->sent($this->logger->getMessages(), $to, $message)->count(),
            "The expected [{$message}] message was not sent."
        );

        return $this;
    }

    public function assertNothingSent()
    {
        PHPUnit::assertTrue(
            0 === $count = count($this->logger->getMessages()),
            "Failed to assert 0 sent SMS messages. [{$count}] were sent."
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function assertNumberOfMessagesSent(int $number)
    {
        PHPUnit::assertTrue(
            $number === $count = count($this->logger->getMessages()),
            "Failed to send [{$number}] SMS messages. [{$count}] were sent."
        );

        return $this;
    }

    /**
     * @param string $to
     * @param bool   $useAlphanumericSender
     *
     * @return mixed|void
     */
    public function sendMessage(TwilioMessage $message, $to, $useAlphanumericSender = false)
    {
        call_user_func_array([$this->logger, 'sendMessage'], func_get_args());
    }

    /**
     * @param array       $twilioRequests
     * @param string      $to
     * @param string      $message
     * @param string|null $connection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function sent($twilioRequests, $to, $message)
    {
        return collect($twilioRequests)->filter(function ($twilioRequest) use ($to, $message) {
            return $twilioRequest->to === $to && $twilioRequest->message === $message;
        });
    }
}
