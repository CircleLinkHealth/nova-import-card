<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AWV;

use App\Notifications\NotificationStrategies\SendsNotification;
use App\Notifications\SendCareDocument;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Notification;

class FaxPatientDocument extends SendsNotification
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
        return $notifiable = Location::whereFax($this->fax)->first()
            ?: Notification::route('phaxio', $this->fax);
    }

    /**
     * @return SendCareDocument
     */
    public function getNotification(): \Illuminate\Notifications\Notification
    {
        return new SendCareDocument($this->document, $this->patient, ['phaxio']);
    }
}
