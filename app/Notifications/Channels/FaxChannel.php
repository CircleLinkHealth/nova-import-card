<?php

namespace App\Notifications\Channels;

use App\Contracts\Efax;
use Illuminate\Notifications\Notification;

class FaxChannel
{
    public function __construct(Efax $fax)
    {
        $this->fax = $fax;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     *
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if ($notifiable->fax) {
            $message = $notification->toFax($notifiable);
            $this->fax->send($notifiable->fax, $message);
        }
    }
}