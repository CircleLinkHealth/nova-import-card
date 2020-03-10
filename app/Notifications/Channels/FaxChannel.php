<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Channels;

use App\Contracts\Efax;
use App\Contracts\FaxableNotification;

class FaxChannel
{
    public function __construct(Efax $fax)
    {
        $this->fax = $fax;
    }

    /**
     * Send the given notification.
     *
     * @param mixed                                  $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     */
    public function send($notifiable, FaxableNotification $notification)
    {
        if ($notifiable->fax) {
            $fax = $this->fax->createFaxFor($notifiable->fax);

            if (method_exists($notification, 'getFaxOptions')) {
                foreach ($notification->getFaxOptions() as $option => $value) {
                    $fax->setOption($option, $value);
                }
            }

            $fax->sendNotification($notifiable, $notification);
        }
    }
}
