<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Events\Pusher;
use App\Notifications\AddendumCreated;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class AddendumNotificationsService
{
    /**
     * Validates if the auth user is allowed to see notification on the client side.
     * Only the author of a note can see a notification about an addendum related to that note.
     *
     * @param $unreadAddendumNotifications
     * @param mixed $authUser
     *
     * @return Collection
     */
    public function addendumNotificationsToPusherVue($unreadAddendumNotifications, $authUser)
    {
        return collect($unreadAddendumNotifications)->map(
            function ($notification) use ($authUser) {
                if ($notification->notifiable_id === $authUser->id) {
                    return $notification;
                }

                return $notification = [];
            }
        );
    }

    /**
     * @param $noteAuthorId
     * @param $addendum
     */
    public function createNotifForAddendum($noteAuthorId, $addendum)
    {
        User::find($noteAuthorId)->notify(new AddendumCreated($addendum));
    }

    /**
     * @param $dataToPusher
     */
    public function dispatchPusherEvent($dataToPusher)
    {
        Pusher::dispatch($dataToPusher);
    }

    /**
     * @param $authUser
     *
     * @return mixed
     */
    public function getUnreadAddendumNotifications($authUser)
    {
        return $authUser->unreadNotifications->map(function ($notification) {
            return $notification;
        })->where('type', '=', 'App\Notifications\AddendumCreated')->all();
    }

    /**
     * @param $addendum
     * @param $noteAuthorId
     */
    public function notifyViaPusher($addendum, $noteAuthorId)
    {
        $dataToPusher = [
            'addendum_author' => $addendum->author_user_id,
            'note_author'     => $noteAuthorId,
        ];

        $this->dispatchPusherEvent($dataToPusher);
    }
}
