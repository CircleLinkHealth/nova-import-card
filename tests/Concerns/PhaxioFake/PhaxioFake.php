<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\PhaxioFake;

use App\Contracts\Efax;
use App\Contracts\FaxableNotification;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Log\LoggerInterface;

class PhaxioFake implements Efax
{
    protected $calls = [];
    protected $connection;
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = new PhaxioFakeLogDriver($logger);
    }

    /**
     * @param string      $to
     * @param string      $file
     * @param string|null $connection
     *
     * @return $this
     */
    public function assertFaxNotSent($to, $file)
    {
        PHPUnit::assertFalse(
            1 === $this->sent($this->logger->getFaxesSent(), $to, $file)->count(),
            "The expected [{$file}] file was not sent."
        );

        return $this;
    }

    /**
     * @param string      $to
     * @param string      $file
     * @param string|null $connection
     *
     * @return $this
     */
    public function assertFaxSent($to, $file)
    {
        PHPUnit::assertTrue(
            1 === $this->sent($this->logger->getFaxesSent(), $to, $file)->count(),
            "The expected [{$file}] file was not sent."
        );

        return $this;
    }

    public function assertNothingSent()
    {
        PHPUnit::assertTrue(
            0 === $count = count($this->logger->getFaxesSent()),
            "Failed to assert 0 sent SMS files. [{$count}] were sent."
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function assertNumberOfFaxesSent(int $number)
    {
        PHPUnit::assertTrue(
            $number === $count = count($this->logger->getFaxesSent()),
            "Failed to send [{$number}] SMS files. [{$count}] were sent."
        );

        return $this;
    }

    public function createFaxFor(string $number): Efax
    {
        return $this;
    }

    public function send(array $options = [])
    {
        return call_user_func_array([$this->logger, 'send'], func_get_args());
    }

    public function sendNotification($notifiable, FaxableNotification &$notification, array $options = [])
    {
        return call_user_func_array([$this->logger, 'sendNotification'], func_get_args());
    }

    public function setOption(string $string, $array): Efax
    {
        return $this;
    }

    /**
     * @param array       $phaxioRequests
     * @param string      $to
     * @param string      $file
     * @param string|null $connection
     *
     * @return Collection
     */
    protected function sent($phaxioRequests, $to, $file)
    {
        return collect($phaxioRequests)->filter(function ($phaxioRequest) use ($to, $file) {
            return $phaxioRequest->to === $to && $phaxioRequest->file === $file;
        });
    }
}
