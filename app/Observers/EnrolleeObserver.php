<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrolleeObserver
{
    public function saved(Enrollee $enrollee)
    {
        if ($this->shouldCreateSurveyOnlyUser($enrollee)) {
            CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
        }
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
