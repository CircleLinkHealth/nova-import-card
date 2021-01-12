<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AWV;

use App\Notifications\NotificationStrategies\SendsNotification;
use App\Notifications\SendCareDocument;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Notification;

class EmailPatientDocument extends SendsNotification
{
    /**
     * @var string
     */
    protected $address;
    /**
     * @var Media
     */
    protected $document;

    /**
     * @var User
     */
    protected $patient;

    /**
     * EmailPatientDocument constructor.
     */
    public function __construct(User $patient, Media $document, string $address)
    {
        $this->patient  = $patient;
        $this->document = $document;
        $this->address  = $address;
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
        return new SendCareDocument($this->document, $this->patient, ['mail']);
    }
}
