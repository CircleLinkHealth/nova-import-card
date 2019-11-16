<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Contracts\RelatesToActivity;
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
     * @param $notificationId
     *
     * @return void
     */
    public function markAsRead($notificationId)
    {
        // is it better if i pass 'read_at' from vue and us it in if() to avoid the $notification::findOrFail() if not needed?
        $notification = DatabaseNotification::findOrFail($notificationId);
        if (empty($notification->read_at)) {
            if ($notification->attachment instanceof RelatesToActivity) {
                $notification->attachment->markActivitiesAsDone();
                $notification->attachment->markAllAttachmentNotificationsAsRead();
            } else {
                $notification->markAsRead();
            }
        }
    }

    /**
     * @param $notification
     *
     * @return Carbon
     */
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
