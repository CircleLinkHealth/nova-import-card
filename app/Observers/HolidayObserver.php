<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Jobs\SendSlackMessage;
use App\Models\Holiday;
use App\NurseContactWindow;

class HolidayObserver
{
    /**
     * Listen for the NurseContactWindow created event.
     *
     * @param NurseContactWindow $holiday
     *
     * @internal param User $user
     */
    public function created(Holiday $holiday)
    {
        if (!app()->environment('production')) {
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
     * @param NurseContactWindow $holiday
     *
     * @internal param User $user
     */
    public function deleted(Holiday $holiday)
    {
        if (!app()->environment('production')) {
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
