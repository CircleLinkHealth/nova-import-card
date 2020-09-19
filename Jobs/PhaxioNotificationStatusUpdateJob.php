<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\SharedModels\Entities\FaxLog;
use CircleLinkHealth\Core\Entities\DatabaseNotification;

class PhaxioNotificationStatusUpdateJob extends NotificationStatusUpdateJob
{
    const CHANNEL_NAME = 'phaxio';
    /**
     * @var DatabaseNotification
     */
    private $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(DatabaseNotification $notification, FaxLog $log, array $props = [])
    {
        parent::__construct($notification->id, self::CHANNEL_NAME, array_merge($props, [
            'value'          => $log->status,
            'details'        => $log->fax_id,
            'event_type'     => $log->event_type,
            'fax_id'         => $log->fax_id,
            'cpm_fax_log_id' => $log->id,
        ]));
        $this->notification = $notification;
    }

    protected function getIdentifier()
    {
        return $this->notification->id;
    }

    protected function getNotification()
    {
        return $this->notification;
    }
}
