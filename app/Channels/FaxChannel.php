<?php

namespace App\Channels;

use App\Contracts\Efax;
use App\Services\Phaxio\PhaxioService;
use Carbon\Carbon;
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
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toFax($notifiable);

        $this->fax->send($notifiable->fax, $message);
    }
}