<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Events\PusherTest;
use App\Notifications\AddendumCreated;
use CircleLinkHealth\Customer\Entities\User;

class AddendumNotificationsService
{
    /**
     * @param $noteAuthorId
     * @param $addendum
     */
    public function createNotifForAddendum($noteAuthorId, $addendum)
    {
        User::find($noteAuthorId)->notify(new AddendumCreated($addendum));
    }

    /**
     * @param $dataToPusher
     */
    public function dispatchPusherEvent($dataToPusher)
    {
        PusherTest::dispatch($dataToPusher);
    }

    /**
     * @param $addendum
     * @param $noteAuthorId
     */
    public function notifyViaPusher($addendum, $noteAuthorId)
    {
        $dataToPusher = [
            'addendum_author' => $addendum->author_user_id,
            'note_author'     => $noteAuthorId,
        ];

        $this->dispatchPusherEvent($dataToPusher);
    }
}
