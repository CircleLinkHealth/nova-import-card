<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Observers;

use CircleLinkHealth\Core\Jobs\SendSlackMessage;
use CircleLinkHealth\Customer\Entities\Holiday;

class HolidayObserver
{
    /**
     * Listen for the NurseContactWindow created event.
     *
     * @param \CircleLinkHealth\Customer\Entities\NurseContactWindow $holiday
     *
     * @internal param User $user
     */
    public function created(Holiday $holiday)
    {
        if ( ! isProductionEnv()) {
            return;
        }

        $auth = auth()->user();

        if ($auth->id != $holiday->nurse->user->id) {
            return;
        }

        $sentence = "Nurse {$auth->getFullName()} will take the day off on {$holiday->date->format('l, F j Y')}";
        $sentence .= ' View Schedule at ';
        $sentence .= route('get.admin.nurse.schedules');

        $job = new SendSlackMessage('#carecoachscheduling', $sentence);

        dispatch($job);
    }

    /**
     * Listen for the NurseContactWindow deleted event.
     *
     * @param \CircleLinkHealth\Customer\Entities\NurseContactWindow $holiday
     *
     * @internal param User $user
     */
    public function deleted(Holiday $holiday)
    {
        if ( ! isProductionEnv()) {
            return;
        }

        $auth = auth()->user();

        if ($auth->id != $holiday->nurse->user->id) {
            return;
        }

        $sentence = "Change of plans y'all! Nurse {$auth->getFullName()} will NOT be taking the day off on {$holiday->date->format('l, F j Y')}";
        $sentence .= ' View Schedule at ';
        $sentence .= route('get.admin.nurse.schedules');

        $job = new SendSlackMessage('#carecoachscheduling', $sentence);

        dispatch($job);
    }
}
