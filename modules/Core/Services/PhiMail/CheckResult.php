<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services\PhiMail;

class CheckResult
{
    /**
     * (Notifications only) Additional status information, if available.
     *
     * @var string|null
     */
    public $info;

    /**
     * Type of CheckResult: true if mail message, false if status notification.
     *
     * @var bool
     */
    public $mail;
    /**
     * The message id to which this result pertains.
     *
     * @var string
     */
    public $messageId;
    /**
     * (Incoming mail only) The number of attachments to the message.
     *
     * @var int|null
     */
    public $numAttachments;
    /**
     * (Incoming mail only) The Direct Address of the recipient
     * as specified by the sender.
     *
     * @var string|null
     */
    public $recipient;
    /**
     * (Incoming mail only) The Direct Address of the sender.
     *
     * @var string|null
     */
    public $sender;
    /**
     * (Notifications only) The status code: failed or dispatched.
     *
     * @var string|null
     */
    public $statusCode;

    /**
     * Is this a mail message?
     *
     * @return bool true if this is an incoming mail message, false otherwise
     */
    public function isMail()
    {
        return $this->mail;
    }

    /**
     * Is this a status notification?
     *
     * @return bool true if this is a status message, false otherwise
     */
    public function isStatus()
    {
        return ! $this->mail;
    }

    /**
     * Create a new CheckResult object for a mail message.
     *
     * @param string $r         the recipient for the message
     * @param string $s         the sender of the message
     * @param int    $numAttach the number of attachments in this message
     * @param string $id        the unique message id to which this notification pertains
     *
     * @return CheckResult
     */
    public static function newMail($r, $s, $numAttach, $id)
    {
        $instance                 = new self();
        $instance->mail           = true;
        $instance->messageId      = $id;
        $instance->statusCode     = null;
        $instance->info           = null;
        $instance->recipient      = $r;
        $instance->sender         = $s;
        $instance->numAttachments = $numAttach;

        return $instance;
    }

    /**
     * Create a new CheckResult object for a status notification.
     *
     * @param string $id     the unique message id to which this notification pertains
     * @param string $status status code (failed or dispatched)
     * @param string $info   additional information, if available
     *
     * @return CheckResult
     */
    public static function newStatus($id, $status, $info)
    {
        $instance                 = new self();
        $instance->mail           = false;
        $instance->messageId      = $id;
        $instance->statusCode     = $status;
        $instance->info           = $info;
        $instance->recipient      = null;
        $instance->sender         = null;
        $instance->numAttachments = 0;

        return $instance;
    }
}
