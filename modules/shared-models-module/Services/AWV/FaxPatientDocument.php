<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\AWV;

use CircleLinkHealth\Core\Notifications\NotificationStrategies\SendsNotification;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Notifications\SendCareDocument;
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
     * @return \CircleLinkHealth\Customer\Notifications\SendCareDocument
     */
    public function getNotification(): \Illuminate\Notifications\Notification
    {
        return new SendCareDocument($this->document, $this->patient, ['phaxio']);
    }
}
