<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\AWV;

use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Core\Notifications\Channels\DirectMailChannel;
use CircleLinkHealth\Core\Notifications\NotificationStrategies\SendsNotification;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Notifications\SendCareDocument;

class DirectPatientDocument extends SendsNotification
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
     */
    public function __construct(User $patient, Media $document, string $address)
    {
        $this->patient  = $patient;
        $this->document = $document;
        $this->address  = $address;
    }

    /**
     * @return
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
}