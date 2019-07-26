<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Events\AddendumPusher;
use App\Models\Addendum;
use App\Notifications\AddendumCreated;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class AddendumNotificationsService
{//constants values are demo
    const ADDENDUM_DESCRIPTION = 'Addendum';
    const ADDENDUM_SUBJECT     = 'has created an addendum for';

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
    public function getAddendumNotifications($authUser)
    {
        return $authUser->notifications->where('type', '=', 'App\Notifications\AddendumCreated')->all();
    }

    /**
     * @return string|null
     */
    public static function getUrlToRedirectUser()
    {
        return session()->previousUrl();
    }

    public function markAddendumNotifAsRead($receiverId, $attachmentId)
    {
        $user = User::find($receiverId);

        $user->unreadNotifications()
            ->hasNotifiableType(User::class)
            ->hasAttachmentType(Addendum::class)
            ->where('attachment_id', '=', $attachmentId)
            ->get()
            ->markAsRead();
    }

    /**
     * @param $addendum
     * @param $noteAuthorId
     */
    public function notifyViaPusher($addendum, $noteAuthorId)
    {
        $dataToPusher = [
            'data' => collect(
                [
                    'sender_id'       => $addendum->author_user_id,
                    'receiver_id'     => $noteAuthorId,
                    'patient_id'      => $addendum->addendumable->patient_id,
                    'note_id'         => $addendum->addendumable_id,
                    'attachment_id'   => $addendum->id,
                    'attachment_type' => Addendum::class,
                    'redirectTo'      => $this->getUrlToRedirectUser(),
                    'description'     => self::ADDENDUM_DESCRIPTION,
                    'subject'         => self::ADDENDUM_SUBJECT,
                ]
            ),
        ];

        $this->dispatchPusherEvent($dataToPusher);
    }

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
