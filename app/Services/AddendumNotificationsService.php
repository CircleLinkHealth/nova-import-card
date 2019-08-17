<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Notifications\AddendumCreated;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class AddendumNotificationsService
{//constants values are demo
    const ADDENDUM_DESCRIPTION = 'Addendum';
    const ADDENDUM_SUBJECT     = 'has created an addendum for';

    public function createNotifForAddendum(User $noteAuthor, $addendum)
    {
        $instance = new AddendumCreated($addendum);
        $noteAuthor->notify(new AddendumCreated($addendum));

        return $instance;
    }

//    /**
//     * @param $dataToPusher
//     */
//    public function dispatchPusherEvent($dataToPusher)
//    {
//        AddendumPusher::dispatch($dataToPusher);
//    }

    /**
     * @param $authUser
     *
     * @return mixed
     */
    public function getAddendumNotifications($authUser)
    {
        return $authUser->notifications->where('type', '=', 'App\Notifications\AddendumCreated')->all();
    }

//    /**
//     * @return string|null
//     */
//    public static function getUrlToRedirectUser()
//    {
//        return session()->previousUrl();
//    }

//    /**
//     * @param Notification $notification
//     * @param $notifiable
//     */
//    public function notifyViaPusher(Notification $notification, $notifiable)
//    {
//        $dataToPusher = [
//            'data' => collect(
//                $notification->toArray($notifiable)
//            ),
//        ];
//
//        $this->dispatchPusherEvent($dataToPusher);
//    }

    /**
     * @param $addendumNotifications
     * @param mixed $authUser
     *
     * @return Collection
     */
    public function whoCanSeeRealTimeNotifications($addendumNotifications, $authUser)
    {
        return collect($addendumNotifications)->map(
            function ($notification) use ($authUser) {
                if ($notification->notifiable_id === $authUser->id) {
                    return $notification;
                }

                return $notification = [];
            }
        );
    }
}
