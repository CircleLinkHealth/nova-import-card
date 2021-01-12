<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Call;

trait ActivityRelatable
{
    public function markActivitiesAsDone()
    {
        $toUpdate = [
            'asap'   => false,
            'status' => Call::DONE,
        ];

        $activities = $this->getActivities();
        $activities->update($toUpdate);
    }

    public function markAllAttachmentNotificationsAsRead()
    {
        $this->markAsReadInNotifications()->map(function ($activity) {
            return $activity->markAttachmentNotificationAsRead(auth()->user());
        });
    }
}
