<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class EnrolleeObserver
{
    public function saved(Enrollee $enrollee)
    {
        if ($this->shouldCreateSurveyOnlyUser($enrollee)) {
            CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
        }

        if (($enrollee->isDirty('provider_id') || $enrollee->isDirty('location_id')) && ! empty($enrollee->medical_record_id)) {
            $updated = Ccda::where('id', $enrollee->medical_record_id)->where('practice_id', $enrollee->practice_id)->update([
                'billing_provider_id' => $enrollee->provider_id,
                'location_id'         => $enrollee->location_id,
            ]);
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
