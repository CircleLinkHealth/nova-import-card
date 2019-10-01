<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Http\Controllers\NotificationController;
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
     * @param $notificationsLimitDate
     *
     * @return Builder[]|Collection|DatabaseNotification[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]
     */
    public function getDropdownNotifications($notificationsLimitDate)
    {
        return DatabaseNotification::whereNotifiableId(auth()->id())
            ->orderByDesc('created_at')
            ->where('created_at', '>=', $notificationsLimitDate)
            ->where('type', NotificationController::NOTIFICATION_TYPE)//Lets keep this here since we show only addendums for now
            ->take(5)
            ->get();
    }

    /**
     * @param $notificationsLimitDate
     *
     * @return int
     */
    public function getDropdownNotificationsCount($notificationsLimitDate)
    {
        return DatabaseNotification::whereNotifiableId(auth()->id())
            ->where('created_at', '>=', $notificationsLimitDate)
            ->where('type', NotificationController::NOTIFICATION_TYPE)//Lets keep this here since we show only addendums for now
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
        $user = User::find($receiverId);

        $user->unreadNotifications()
            ->where('attachment_id', '=', $attachmentId)
            ->get()
            ->markAsRead();
    }

    public function notificationCreatedAt($notification)
    {
        return Carbon::parse($notification->created_at);
    }

    /**
     * @param Carbon $createdDateTime
     *
     * @return string
     */
    public function notificationElapsedTime(Carbon $createdDateTime)
    {
        return $createdDateTime->diffForHumans(Carbon::parse(now()));
    }

    /**
     * @param Collection $notifications
     *
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
