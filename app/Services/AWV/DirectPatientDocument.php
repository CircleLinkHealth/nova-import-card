<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AWV;

use App\Contracts\SendsNotification;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\SendCareDocument;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;

class DirectPatientDocument implements SendsNotification
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
     * DirectPatientDocument constructor.
     *
     * @param User   $patient
     * @param Media  $document
     * @param string $address
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
        return User::whereHas('emrDirect', function ($emr) {
            $emr->where('address', $this->address);
        })->first()
        ?:
            (Location::whereHas('emrDirect', function ($emr) {
                $emr->where('address', $this->address);
            })->first()
                ?: Notification::route(DirectMailChannel::class, $this->address));
    }

    /**
     * @return SendCareDocument
     */
    public function getNotification(): \Illuminate\Notifications\Notification
    {
        return new SendCareDocument($this->document, $this->patient, [DirectMailChannel::class]);
    }

    public function send()
    {
        $this->getNotifiable()->notify($this->getNotification());
    }
}
