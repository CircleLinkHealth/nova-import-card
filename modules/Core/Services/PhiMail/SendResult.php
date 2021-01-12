<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services\PhiMail;

class SendResult
{
    /**
     * Additional error information, if available, when $succeeded == false.
     *
     * @var string|null
     */
    public $errorText;
    /**
     * The unique message id assigned to the message for the given recipient
     * when $succeeded == true. This value is not set if the message could
     * not be sent. This id should be used to correlate with any subsquent
     * status notifications.
     *
     * @var string|null
     */
    public $messageId;

    /**
     * The recipient to whom this result object pertains.
     *
     * @var string
     */
    public $recipient;
    /**
     * True if transmission to the recipient succeeded, false otherwise.
     *
     * @var bool
     */
    public $succeeded;

    public function __construct($r, $s, $m)
    {
        $this->recipient = $r;
        $this->succeeded = $s;
        if ($s) {
            $this->messageId = $m;
        } else {
            $this->errorText = $m;
        }
    }
}
