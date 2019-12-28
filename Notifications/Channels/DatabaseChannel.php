<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function send($notifiable, Notification $notification)
    {

        $args = [
            'id'      => $notification->id,
            'type'    => get_class($notification),
            'data'    => $this->getData($notifiable, $notification),
            'read_at' => null,
        ];

        if (method_exists($notification, 'getAttachment')) {
            $args['attachment_id']   = $notification->getAttachment()->id;
            $args['attachment_type'] = get_class($notification->getAttachment());
        }

        if (is_a($notifiable, AnonymousNotifiable::class)) {
            $args['notifiable_id']   = $notifiable->id;
            $args['notifiable_type'] = AnonymousNotifiable::class;
        }
        return $notifiable->routeNotificationFor('database')->create($args);
    }

    /**
     * Get the data for the notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getData($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toDatabase')) {
            return is_array($data = $notification->toDatabase($notifiable))
                ? $data
                : $data->data;
        }

        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }

        throw new RuntimeException(
            'Notification is missing toDatabase / toArray method.'
        );
    }
}
