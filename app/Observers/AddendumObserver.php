<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Models\Addendum;
use App\Note;
use App\Notifications\AddendumCreated;
use Illuminate\Support\Facades\Notification;

class AddendumObserver
{
    /**
     * Handle the addendum "created" event.
     *
     * @param Addendum $addendum
     */
    public function created(Addendum $addendum)
    {
        if (is_a($addendum->addendumable, Note::class)) {
            $noteAuthorUser = $addendum->addendumable->author;

            if (is_a($noteAuthorUser, User::class) && auth()->id() !== optional($noteAuthorUser)->id) {
                Notification::send($noteAuthorUser, new AddendumCreated($addendum, auth()->user()));
            }
        }
    }
}
