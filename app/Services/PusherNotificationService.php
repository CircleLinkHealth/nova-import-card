<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Customer\Entities\User;

class PusherNotificationService
{
    /**
     * @param $receiverId
     * @param $attachmentId
     */
    public function markNotificationAsRead($receiverId, $attachmentId)
    {
        $user = User::find($receiverId);

        $user->unreadNotifications()
            ->where('attachment_id', '=', $attachmentId)
            ->get()
            ->markAsRead();
    }
}
