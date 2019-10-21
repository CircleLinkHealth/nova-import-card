<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Notifications\NotificationStrategies\SendsNotification;
use App\Notifications\SendCareDocument;
use App\Notifications\SendPatientEmail;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\Entities\Media;
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

    /**
     * @var int
     */
    protected $noteId;

    /**
     * EmailPatientDocument constructor.
     *
     * @param string $content
     * @param string $address
     * @param mixed  $attachments
     * @param null   $noteId
     */
    public function __construct(string $content, $address, $attachments = [], $noteId = null)
    {
        $this->content     = $content;
        $this->address     = $address;
        $this->attachments = $attachments;
        $this->noteId      = $noteId;
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
        return new SendPatientEmail($this->content, $this->attachments, $this->noteId);
    }
}
