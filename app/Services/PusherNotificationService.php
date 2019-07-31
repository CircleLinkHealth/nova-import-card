<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Events\PusherNotificationCreated;
use CircleLinkHealth\Customer\Entities\User;

class PusherNotificationService
{
    /**
     * @param $dataToPusher
     */
    public function dispatchPusherEvent($dataToPusher)
    {
        PusherNotificationCreated::dispatch($dataToPusher);
    }

    /**
     * @return string|null
     */
    public static function getUrlToRedirectUser()
    {
        return session()->previousUrl();
    }

    /**
     * @param $receiverId
     * @param $attachmentId
     */
    public function markNotificationAsRead($receiverId, $attachmentId)
    {
        $user = User::find($receiverId);

        $user->unreadNotifications()
            ->where('attachment_id', '=', $attachmentId)
            ->get()
            ->markAsRead(); //include type also
    }

    /**
     * @param \App\Contracts\PusherNotification $notification
     */
    public function notifyViaPusher(\App\Contracts\PusherNotification $notification)
    {
        $dataToPusher = [
            'data' => [
                $notification->toPusher(),
            ],
        ];

        $this->dispatchPusherEvent($dataToPusher);
    }
}
