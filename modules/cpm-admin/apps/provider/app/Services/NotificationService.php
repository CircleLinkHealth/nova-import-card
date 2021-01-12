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
    const NOTIFICATION_PER_PAGE_DEFAULT = 5;

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
     * @param $notification
     *
     * @return mixed
     */
    public function countUserNotifications()
    {
        $user = auth()->user();

        return $this->getUserNotifications($user)->count();
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
     * @param $page
     * @param $notificationsPerPage
     *
     * @return Collection
     */
    public function getPaginatedNotifications($page, $notificationsPerPage)
    {
        $user              = auth()->user();
        $userNotifications = $this->getUserNotifications($user)->forPage($page, $notificationsPerPage);

        return $this->prepareNotifications($userNotifications);
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
     * @param $user
     *
     * @return mixed
     */
    public function getUserNotifications($user)
    {
        return $user->notifications()
            ->liveNotification()
            ->get();
    }

    /**
     * @param $notificationId
     *
     * @return void
     */
    public function markAsRead($notificationId)
    {
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
        return $createdDateTime->diffForHumans();
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
