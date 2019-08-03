<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use App\Contracts\PusherNotification;
use CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

abstract class PusherEvent implements PusherNotification, ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $dataToPusher;
    /**
     * @var Notification
     */
    private $notification;

    /**
     * Create a new event instance.
     *
     * @param Notification $notification
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
        $this->setDataToPusher();
        $this->storeNotificationForReceivers($notification);
        $this->dontBroadcastToCurrentUser();
    }

    public function setDataToPusher()
    {
        if ( ! $this->notification->id) {
            $this->notification->id = Str::uuid();
        }

        $this->dataToPusher = [
            'notificationId' => $this->notification->id,
        ];
    }

    /**
     * @param Notification $notification
     */
    private function storeNotificationForReceivers($notification)
    {
        $dbChannel = app(DatabaseChannel::class);

        foreach ($this->receivers() as $receiver) {
            if ( ! is_a($receiver, User::class)) {
                $receiver = User::findOrFail($receiver);
            }

            //save the notification in the DB first
            $dbChannel->send($receiver, $notification);
        }
    }
}
