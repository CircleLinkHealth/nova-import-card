<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
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
        if ($this->shouldCreateSurveyOnlyUser($enrollee)) {
            CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
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

    private function shouldCreateSurveyOnlyUser(Enrollee $enrollee)
    {
        if ( ! $enrollee->isDirty('status')) {
            return false;
        }

        if ( ! is_null($enrollee->user_id)) {
            return false;
        }

        if (Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->getOriginal('status')) {
            return false;
        }

        if (Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->status) {
            return true;
        }

        return false;
    }
}
