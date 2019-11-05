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
     * @param $notifiable
     * @param $senderId
     * @param $patientName
     * @param $noteId
     * @param $attachmentId
     * @param $redirectLink
     * @param $description
     * @param $class
     * @param $subject
     * @param $senderName
     *
     * @return array
     */
    public static function getNotificationArrayRepresentation(
        $notifiable,
        $senderId,
        $patientName,
        $noteId,
        $attachmentId,
        $redirectLink,
        $description,
        $class,
        $subject,
        $senderName
    ) {
        return [
            'sender_id'       => $senderId,
            'receiver_id'     => $notifiable->id,
            'patient_name'    => $patientName,
            'note_id'         => $noteId, //Need to rename to a more generic name. Not all notif. will have note_id
            'attachment_id'   => $attachmentId,
            'redirect_link'   => $redirectLink,
            'attachment_type' => $class,
            'description'     => $description,
            'subject'         => $subject,
            'sender_name'     => $senderName,
        ];
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
