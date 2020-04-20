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
