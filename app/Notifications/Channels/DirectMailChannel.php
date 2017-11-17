<?php

namespace App\Notifications\Channels;

use App\Contracts\DirectMail;
use Illuminate\Notifications\Notification;

class DirectMailChannel
{
    protected $dm;

    public function __construct(DirectMail $dm)
    {
        $this->dm = $dm;
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
        $filePath = $notification->toDirectMail($notifiable);

        $fileName = str_substr_after($filePath, '/');

        $this->dm->send($notifiable->emr_direct_address, $filePath, $fileName);
    }
}