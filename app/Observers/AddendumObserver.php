<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

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
        \App\Events\AddendumCreated::dispatch(new AddendumCreated($addendum));
    }
}
