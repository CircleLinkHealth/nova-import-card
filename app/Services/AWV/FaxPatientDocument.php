<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AWV;

use App\Contracts\SendsNotification;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\SendCareDocument;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Notification;

class FaxPatientDocument implements SendsNotification
{
    /**
     * @var Media
     */
    protected $document;

    /**
     * @var string
     */
    protected $fax;

    /**
     * @var User
     */
    protected $patient;

    /**
     * DirectPatientDocument constructor.
     *
     * @param User   $patient
     * @param Media  $document
     * @param string $fax
     */
    public function __construct(User $patient, Media $document, string $fax)
    {
        $this->patient  = $patient;
        $this->document = $document;
        $this->fax      = $fax;
    }

    /**
     * @return
     */
    public function getNotifiable()
    {
        return $notifiable = Location::whereFax($this->fax)->first() ?: Notification::route(FaxChannel::class, $this->fax);
    }

    /**
     * @return SendCareDocument
     */
    public function getNotification(): \Illuminate\Notifications\Notification
    {
        return new SendCareDocument($this->document, $this->patient, [FaxChannel::class]);
    }

    public function send()
    {
        $this->getNotifiable()->notify($this->getNotification());
    }
}
