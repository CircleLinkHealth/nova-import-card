<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/17/2017
 * Time: 3:17 PM
 */

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use RuntimeException;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
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

        return $notifiable->routeNotificationFor('database')->create($args);
    }

    /**
     * Get the data for the notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     *
     * @return array
     *
     * @throws \RuntimeException
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
