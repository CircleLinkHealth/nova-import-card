<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Events\AddendumPusher;
use App\Notifications\AddendumCreated;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class AddendumNotificationsService
{//constants values are demo
    const ADDENDUM_DESCRIPTION = 'Addendum';
    const ADDENDUM_SUBJECT     = 'has created an addendum for';

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
        AddendumPusher::dispatch($dataToPusher);
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
            'sender_id'   => $addendum->author_user_id,
            'receiver_id' => $noteAuthorId,
            'patient_id'  => $addendum->addendumable->patient_id,
            'description' => self::ADDENDUM_DESCRIPTION,
            'subject'     => self::ADDENDUM_SUBJECT,
        ];

        $this->dispatchPusherEvent($dataToPusher);
    }
}
