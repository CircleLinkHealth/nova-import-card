<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Jobs\CreateUsersFromEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrolleeObserver
{
    public function saved(Enrollee $enrollee)
    {
        if ($enrollee->isDirty('status')) {
            if (Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->status
            && is_null($enrollee->user_id)) {
                CreateUsersFromEnrollees::dispatch();
            }
        }
    }
}
