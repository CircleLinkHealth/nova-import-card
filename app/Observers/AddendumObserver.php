<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Events\PusherNotificationCreated;
use App\Models\Addendum;
use App\Notifications\AddendumCreated;
use App\PusherNotifier;
use App\Services\AddendumNotificationsService;

class AddendumObserver
{
    public $service;

    public function __construct(AddendumNotificationsService $addendumNotification)
    {
        $this->service = $addendumNotification;
    }

    /**
     * Handle the addendum "created" event.
     *
     * @param Addendum $addendum
     */
    public function created(Addendum $addendum)
    {
        $notification = new AddendumCreated($addendum);
        PusherNotificationCreated::dispatch($notification);

        //@todo:consider deleting this

//        $addendum->loadMissing(['addendumable.author', 'addendumable.patient']);
//        $noteAuthor = $addendum->addendumable->author;
//
//        $notification = $this->service->createNotifForAddendum($noteAuthor, $addendum);

//        $this->service->notifyViaPusher($notification, $noteAuthor);

//        $notifier = new PusherNotifier($notification, $noteAuthor);
    }
}
