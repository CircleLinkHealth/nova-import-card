<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Call;
use App\Models\Addendum;
use Illuminate\Notifications\DatabaseNotification;

class NotificationObserver
{
    public function saved(DatabaseNotification $notification)
    {
        $readOnlyNotifications = [
            'addendum' => Addendum::class,
        ];

        if ($notification->isDirty('read_at')
            && in_array($notification->attachment_type, $readOnlyNotifications)
            && ! empty($notification->read_at)) {
            $type     = array_search($notification->attachment_type, $readOnlyNotifications);
            $toUpdate = [
                'asap'   => false,
                'status' => 'done',
            ];
            Call::where('type', $type)
                ->where('note_id', $notification->attachment->addendumable->id)
                ->where('outbound_cpm_id', $notification->notifiable_id)
                ->update($toUpdate);
        }
    }
}
