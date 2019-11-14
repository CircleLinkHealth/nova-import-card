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
    /**
     * When the a notification is marked as read, if the related activity is a "read only" activity,
     * meaning it requires no action from user to be marked as done/reached,
     * will be marked as done/reached if it is included in $readOnlyNotifications[].
     */
    public function saved(DatabaseNotification $notification)
    {
//        $readOnlyNotifications = [
//            'addendum' => Addendum::class,
//        ];
//
//        if ($notification->isDirty('read_at')
//            && in_array($notification->attachment_type, $readOnlyNotifications)
//            && ! empty($notification->read_at)) {
//            $type     = array_search($notification->attachment_type, $readOnlyNotifications);
//            $toUpdate = [
//                'asap'   => false,
//                'status' => 'done',
//            ];
//            //@todo: update many when change Notifications markAsRead to mark multiple
//            Call::where('type', $type)
//                ->where('note_id', $notification->attachment->addendumable->id)
//                ->where('outbound_cpm_id', $notification->notifiable_id)
//                ->update($toUpdate); //future note: this in not triggering CallObserver.
//        }
    }
}
