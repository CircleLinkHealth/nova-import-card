<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * @param $notification
     * @param $createdDateTime
     *
     * @return string
     */
    public function addElapsedTime($notification, $createdDateTime)
    {
        return $notification['elapsed_time'] = $this->notificationElapsedTime($createdDateTime);
    }

    /**
     * @return Collection
     */
    public function getAllUserNotifications()
    {
        $user              = auth()->user();
        $userNotifications = $user->notifications()->liveNotification()->get();
        //@todo: pagination needed
        return $this->prepareNotifications($userNotifications);
    }

    /**
     * @return Builder[]|Collection|DatabaseNotification[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]
     */
    public function getDropdownNotifications()
    {
        return DatabaseNotification::whereNotifiableId(auth()->id())
            ->orderByDesc('created_at')
            ->liveNotification()
            ->take(5)
            ->get();
    }

    /**
     * @return int
     */
    public function getDropdownNotificationsCount()
    {
        return DatabaseNotification::whereNotifiableId(auth()->id())
            ->liveNotification()
            ->where('read_at', null)
            ->count();
    }

    /**
     * @param $patientId
     *
     * @return mixed|string
     */
    public static function getPatientName($patientId)
    {
        return User::find($patientId)->display_name;
    }

    /**
     * @param $id
     *
     * @return DatabaseNotification|DatabaseNotification[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function getPusherNotificationData($id)
    {
        return DatabaseNotification::findOrFail($id);
    }

    /**
     * @param $receiverId
     * @param $attachmentId
     */
    public function markAsRead($receiverId, $attachmentId)
    {
        $notification = DatabaseNotification::whereAttachmentId($attachmentId)->first();
        if (empty($notification->read_at)) {
            $notification->markAsRead();
        }
    }

    public function notificationCreatedAt($notification)
    {
        return Carbon::parse($notification->created_at);
    }

    /**
     * @return string
     */
    public function notificationElapsedTime(Carbon $createdDateTime)
    {
        return $createdDateTime->diffForHumans(Carbon::parse(now()));
    }

    /**
     * @return Collection
     */
    public function prepareNotifications(Collection $notifications)
    {
        return $notifications->map(function ($notification) {
            $createdDateTime = $this->notificationCreatedAt($notification);
            $this->addElapsedTime($notification, $createdDateTime);

            return $notification;
        });
    }
}
