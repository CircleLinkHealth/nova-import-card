<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Observers;

use CircleLinkHealth\SharedModels\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\UnresolvedPostmarkCallback;

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
        if (Enrollee::TO_CALL === $enrollee->getOriginal('status')
            && $enrollee->wasChanged('status')) {
            $unresolvedCall = UnresolvedPostmarkCallback::where('user_id', $enrollee->user_id)->first();
            if ($unresolvedCall) {
                try {
                    $unresolvedCall->delete();
                } catch (\Exception $e) {
                    \Log::warning("Failed to update unresolved_callbacks for user $enrollee->user_id");
                    sendSlackMessage('#carecoach_ops_alerts', "Patient with id $enrollee->user_id status was updated by Care Ambassador,
                    but CPM failed to update Unresolved Callback Dashboard. You can manually Archive this patient from the dashboard");
                }
            }
        }

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
