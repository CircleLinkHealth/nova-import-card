<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\CarePlanWasProviderApproved;
use App\Events\CarePlanWasRNApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyPatientOfCarePlanApproval implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle($event)
    {
        if ($this->shouldSkip($event)) {
            return;
        }
        $event->patient->carePlan->fresh()->notifyPatientOfApproval();
    }

    /**
     * This listener is raised on both {@link CarePlanWasRNApproved} and {@link CarePlanWasProviderApproved}.
     * If auto-approve is enabled, these events will happen almost at the same time.
     * With this check, we skip notifying when first event is raised.
     *
     * @param $event
     */
    private function shouldSkip($event): bool
    {
        return is_a($event, CarePlanWasRNApproved::class) && (bool) $event->patient->primaryPractice->cpmSettings()->auto_approve_careplans;
    }
}
