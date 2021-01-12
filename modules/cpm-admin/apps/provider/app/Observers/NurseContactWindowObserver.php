<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\Entities\NurseContactWindow;

class NurseContactWindowObserver
{
    /**
     * Listen for the NurseContactWindow created event.
     *
     * @internal param User $user
     */
    public function created(NurseContactWindow $window)
    {
        $auth = auth()->user();

        if ( ! $auth) {
            return;
        }

        if ($auth->id != $window->nurse->user->id) {
            return;
        }

        $sentence = "Nurse {$auth->getFullName()} has just created a new Window for ";
        $sentence .= "{$window->dayName}, {$window->date->format('m-d-Y')} from {$window->range()->start->format('h:i A T')} to {$window->range()->end->format('h:i A T')}. View Schedule at ";
        $sentence .= route('get.admin.nurse.schedules');

        \sendSlackMessage('#carecoachscheduling', $sentence);
    }

    /**
     * Listen for the NurseContactWindow deleted event.
     *
     * @internal param User $user
     */
    public function deleted(NurseContactWindow $window)
    {
        $auth = auth()->user();

        if ($auth->id != $window->nurse->user->id) {
            return;
        }

        $sentence = "Nurse {$auth->getFullName()} has just deleted the Window for ";
        $sentence .= "{$window->dayName}, {$window->date->format('m-d-Y')} from {$window->range()->start->format('h:i A T')} to {$window->range()->end->format('h:i A T')}. View Schedule at ";
        $sentence .= route('get.admin.nurse.schedules');

        \sendSlackMessage('#carecoachscheduling', $sentence);
    }
}
