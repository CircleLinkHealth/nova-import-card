<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\TwilioFake;

use App\Contracts\TwilioInterface;
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
        $this->logger = new LogDriver($logger);
    }

    /**
     * @param string      $to
     * @param string      $message
     * @param string|null $connection
     *
     * @return $this
     */
    public function assertMessageNotSent($to, $message, $connection = null)
    {
        PHPUnit::assertFalse(
            1 === $this->sent($this->logger->getMessages(), $to, $message, $connection)->count(),
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
    public function assertMessageSent($to, $message, $connection = null)
    {
        PHPUnit::assertTrue(
            1 === $this->sent($this->logger->getMessages(), $to, $message, $connection)->count(),
            "The expected [{$message}] message was not sent."
        );

        return $this;
    }

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
    protected function sent($twilioRequests, $to, $message, $connection)
    {
        return collect($twilioRequests)->filter(function ($twilioRequest) use ($to, $message, $connection) {
            return $twilioRequest->to === $to && $twilioRequest->message === $message && (
                is_null($connection) || $twilioRequest->connection === $connection
            );
        });
    }
}
