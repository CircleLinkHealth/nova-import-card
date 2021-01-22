<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

/**
 * This interface is to be implemented by any Model that can be attached to a Notification.
 * Note, that "attached" does not mean that we are actually attaching a file to a notification, such as the case of
 * email attachments. It only means that the Model is related to a Notification.
 *
 * Interface AttachableToNotification
 */
interface AttachableToNotification
{
    /**
     * Mark the Notification this Model is attached to as read.
     *
     * @param $notifiable
     */
    public function markAttachmentNotificationAsRead($notifiable);

    /**
     * Returns the notifications that included this model as an attachment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications();
}
