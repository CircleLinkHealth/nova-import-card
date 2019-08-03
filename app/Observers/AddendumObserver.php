<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Events\AddendumCreatedEvent;
use App\Models\Addendum;
use App\Notifications\AddendumCreated;

class AddendumObserver
{
    /**
     * Handle the addendum "created" event.
     *
     * @param Addendum $addendum
     */
    public function created(Addendum $addendum)
    {
        AddendumCreatedEvent::dispatch(new AddendumCreated($addendum));
    }
}
