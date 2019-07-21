<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Models\Addendum;
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
        $noteAuthorId = $addendum->addendumable->author_id;
        $this->service->createNotifForAddendum($noteAuthorId, $addendum);
        $this->service->notifyViaPusher($addendum, $noteAuthorId);
    }
}
