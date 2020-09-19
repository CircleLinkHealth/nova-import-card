<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\PhaxioFake;

use CircleLinkHealth\Core\Contracts\Efax;
use CircleLinkHealth\Core\Contracts\FaxableNotification;
use App\Notifications\Channels\FaxChannel;
use Phaxio\Fax;
use Psr\Log\LoggerInterface;

class PhaxioFakeLogDriver implements Efax
{
    /**
     * @var array
     */
    protected $sendMethodCalls = [];
    /**
     * @var array
     */
    protected $sendNotificationMethodCalls = [];
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createFaxFor(string $number): Efax
    {
        return $this;
    }

    public function getFaxesSent(): array
    {
        return $this->sendMethodCalls;
    }

    public function send(array $options = [])
    {
        $this->sendMethodCalls[] = (object) $options;

        $this->logger->info('Fax sent');

        return new Fax($this);
    }

    public function sendNotification($notifiable, FaxableNotification $notification, array $options = [])
    {
        $this->sendNotificationMethodCalls[] = (object) func_get_args();

        if ( ! array_key_exists('to', $options)) {
            $options['to'] = FaxChannel::getFaxNumber($notifiable);
        }

        if ( ! array_key_exists('file', $options)) {
            $options['file'] = $notification->filePath ?? $notification->pathToPdf ?? null;
        }

        $this->logger->info('Fax sent');

        return $this->send($options);
    }

    public function setOption(string $string, $array): Efax
    {
        return $this;
    }
}
