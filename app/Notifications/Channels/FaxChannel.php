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
        if ($faxNumber = $this->getFaxNumber($notifiable)) {
            $fax = $this->fax->createFaxFor($faxNumber);

            if (method_exists($notification, 'getFaxOptions')) {
                foreach ($notification->getFaxOptions() as $option => $value) {
                    $fax->setOption($option, $value);
                }
            }

            $fax->sendNotification($notifiable, $notification);
        }
    }

    private function getFaxNumber($notifiable)
    {
        if ($fax = $notifiable->fax ?? null) {
            return $fax;
        }
        if ($fax = $notifiable->routeNotificationFor(FaxChannel::class) ?? null) {
            return $fax;
        }
        if ($fax = $notifiable->routeNotificationFor('phaxio') ?? null) {
            return $fax;
        }

        return null;
    }
}
