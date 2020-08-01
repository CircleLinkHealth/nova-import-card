<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\TwilioFake;

use CircleLinkHealth\Core\TwilioInterface;
use NotificationChannels\Twilio\TwilioMessage;
use Psr\Log\LoggerInterface;

class TwilioFakeLogDriver implements TwilioInterface
{
    protected $messages = [];
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function sendMessage(TwilioMessage $message, $to, $useAlphanumericSender = false)
    {
        $this->messages[] = (object) [
            'to'                    => $to,
            'message'               => $message->content,
            'useAlphanumericSender' => $useAlphanumericSender,
        ];

        $verb   = $useAlphanumericSender ? 'using' : 'not using';
        $logMsg = "Sending a message [\"{$message->content}\"] to {$to}, $verb Alphanumeric Sender.";

        $this->logger->info($logMsg);

//        This will log the message in the console at test runtime
//        fwrite(STDOUT, $logMsg);
    }
}
