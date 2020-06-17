<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

use CircleLinkHealth\Core\Entities\AnonymousNotifiable;
use Illuminate\Notifications\Channels\DatabaseChannel as LaravelDatabaseChannel;
use Illuminate\Notifications\Notification;

class DatabaseChannel extends LaravelDatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        $res = parent::send($notifiable, $notification);

        // in case we are in a DB transaction.
        // we want to make sure we have the notification in DB, so we can update status in case it fails
        \DB::commit();

        return $res;
    }

    /**
     * Build an array payload for the DatabaseNotification Model.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    protected function buildPayload($notifiable, Notification $notification)
    {
        $payload = parent::buildPayload($notifiable, $notification);

        if (method_exists($notification, 'getAttachment')) {
            $payload['attachment_id']   = $notification->getAttachment()->id;
            $payload['attachment_type'] = get_class($notification->getAttachment());
        }

        if ($notifiable instanceof AnonymousNotifiable) {
            $payload['notifiable_id']   = $notifiable->id;
            $payload['notifiable_type'] = AnonymousNotifiable::class;
        }

        return $payload;
    }
}
