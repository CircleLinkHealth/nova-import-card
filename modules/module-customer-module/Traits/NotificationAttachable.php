<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

trait NotificationAttachable
{
    /**
     * Mark the Notification this Model is attached to as read.
     *
     * @param $notifiable
     */
    public function markAttachmentNotificationAsRead($notifiable)
    {
        $notifiable->unreadNotifications()
            ->hasNotifiableType(get_class($notifiable))
            ->hasAttachmentType(self::class)
            ->where('attachment_id', '=', $this->id)
            ->get()
            ->markAsRead();
    }

    /**
     * Returns the notifications that included this model as an attachment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(\CircleLinkHealth\Core\Entities\DatabaseNotification::class, 'attachment')
            ->orderBy('created_at', 'desc');
    }
}
