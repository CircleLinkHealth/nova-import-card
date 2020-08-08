<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Notifications\NotificationStrategies\SendsNotification;
use App\Notifications\SendCareDocument;
use App\Notifications\SendPatientEmail;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\Entities\User;
use Notification;

class PatientCustomEmail extends SendsNotification
{
    /**
     * @var string
     */
    protected $address;
    /**
     * @var array
     */
    protected $attachments;

    /**
     * @var string
     */
    protected $content;

    protected $emailSubject;

    /**
     * @var int
     */
    protected $noteId;

    protected $patient;

    protected $senderId;

    /**
     * EmailPatientDocument constructor.
     *
     * @param mixed  $senderId
     * @param string $address
     * @param mixed  $attachments
     * @param null   $noteId
     * @param string $emailSubject
     */
    public function __construct(
        User $patient,
        $senderId,
        string $content,
        $address,
        array $attachments = [],
        $noteId = null,
        $emailSubject
    ) {
        $this->patient      = $patient;
        $this->senderId     = $senderId;
        $this->content      = $content;
        $this->address      = $address;
        $this->attachments  = $attachments;
        $this->noteId       = $noteId;
        $this->emailSubject = $emailSubject;
    }

    /**
     * @return Notifiable
     */
    public function getNotifiable()
    {
        return User::whereEmail($this->address)->first()
            ?: Notification::route('mail', $this->address);
    }

    /**
     * @return SendCareDocument
     */
    public function getNotification(): \Illuminate\Notifications\Notification
    {
        return new SendPatientEmail(
            $this->patient,
            $this->senderId,
            $this->content,
            $this->attachments,
            $this->noteId,
            $this->emailSubject
        );
    }
}
