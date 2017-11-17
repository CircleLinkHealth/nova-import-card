<?php

namespace App\Channels;

use App\Services\Phaxio\PhaxioService;
use Illuminate\Notifications\Notification;

class DirectMailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toFax($notifiable);


    }
}