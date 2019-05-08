<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Call;
use App\CallView;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;

/**
 * Class PatientContactWindowObserver.
 */
class PatientContactWindowObserver
{
    /**
     * Listen for the PatientContactWindow created event.
     *
     * @param \CircleLinkHealth\Customer\Entities\PatientContactWindow $window
     */
    public function created(PatientContactWindow $window)
    {
        $this->updateScheduledActivitiesWithNewContactWindow($window);
    }

    /**
     * Listen for the NurseContactWindow deleted event.
     *
     * @param \CircleLinkHealth\Customer\Entities\PatientContactWindow $window
     */
    public function deleted(PatientContactWindow $window)
    {
    }

    /**
     * Listen for the PatientContactWindow update event.
     *
     * @param \CircleLinkHealth\Customer\Entities\PatientContactWindow $window
     */
    public function updated(PatientContactWindow $window)
    {
        $this->updateScheduledActivitiesWithNewContactWindow($window);
    }

    private function updateScheduledActivitiesWithNewContactWindow(PatientContactWindow $window)
    {
        // @var $auth \CircleLinkHealth\Customer\Entities\User
        $auth = auth()->user();

        if ( ! $auth) {
            return;
        }

        if ( ! $auth->isCareCoach()) {
            return;
        }

        $patientId = $window->patient_info->user_id;

        $calls = CallView::where('patient_id', '=', $patientId)
            ->where('status', '=', 'scheduled')
            ->whereIn('scheduler', ['core algorithm', 'rescheduler algorithm'])
            ->get();

        if ($calls->isEmpty()) {
            return;
        }

        $contactDays = $window->patient_info->contactWindows->pluck('day_of_week')->toArray();

        //NOTE:
        //not recommended way to update sql entries
        //each loop does a trip to the database,
        //which is a sign of bad practice (bad for performance)
        //however, for this use case, we know that only a small number of calls
        //will be updated (usually 1)
        $calls->each(
            function (Call $c) use ($window, $contactDays) {
                $hasChange = false;

                $scheduledDate = Carbon::fromSerialized($c->scheduled_date);
                if ( ! in_array($scheduledDate->dayOfWeek, $contactDays)) {
                    //get next day of week
                    $nextDay = $contactDays[0];
                    $diff = $scheduledDate->dayOfWeek - $nextDay;
                    if ($diff > 0) {
                        $scheduledDate->addDay($diff);
                    }
                    //todo: try with next day

                    $c->scheduled_date = $scheduledDate->format('Y-m-d');
                    $hasChange = true;
                }

                if ($c->window_start != $window->window_time_start) {
                    $c->window_start = $window->window_time_start;
                    $hasChange = true;
                }

                if ($c->window_end != $window->window_time_end) {
                    $c->window_end = $window->window_time_end;
                    $hasChange = true;
                }

                if ($hasChange) {
                    $c->save();
                }
            }
        );
    }
}
