<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Jobs\CreateUsersFromEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrolleeObserver
{
    /**
     * Handle the enrollee "created" event.
     *
     * @return void
     */
    public function created(Enrollee $enrollee)
    {
    }

    /**
     * Handle the enrollee "deleted" event.
     *
     * @return void
     */
    public function deleted(Enrollee $enrollee)
    {
    }

    /**
     * Handle the enrollee "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Enrollee $enrollee)
    {
    }

    /**
     * Handle the enrollee "restored" event.
     *
     * @return void
     */
    public function restored(Enrollee $enrollee)
    {
    }

    public function saved(Enrollee $enrollee)
    {
        if ($enrollee->isDirty('status') && is_null($enrollee->user_id)) {
            if (Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->status) {
                CreateUsersFromEnrollees::dispatch([$enrollee->id]);
            }
        }
    }

    /**
     * Handle the enrollee "updated" event.
     *
     * @return void
     */
    public function updated(Enrollee $enrollee)
    {
    }
}
