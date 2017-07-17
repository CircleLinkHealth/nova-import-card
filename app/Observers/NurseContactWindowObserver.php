<?php

namespace App\Observers;


use App\NurseContactWindow;
use Maknz\Slack\Facades\Slack;

class NurseContactWindowObserver
{
    /**
     * Listen for the NurseContactWindow created event.
     *
     * @param NurseContactWindow $window
     *
     * @internal param User $user
     */
    public function created(NurseContactWindow $window)
    {
        if (!app()->environment('production')) {
            return;
        }

        $auth = auth()->user();

        if ($auth->id != $window->nurse->user->id) {
            return;
        }

        $sentence = "Nurse $auth->fullName has just created a new Window for ";
        $sentence .= "$window->dayName, {$window->date->format('m-d-Y')} from $window->window_time_start to $window->window_time_end. View Schedule at ";
        $sentence .= route('get.admin.nurse.schedules');

//        Slack::to('#callcenter_engagement')->send($sentence);
    }


    /**
     * Listen for the NurseContactWindow deleted event.
     *
     * @param NurseContactWindow $window
     *
     * @internal param User $user
     */
    public function deleted(NurseContactWindow $window)
    {
        if (!app()->environment('production')) {
            return;
        }

        $auth = auth()->user();

        if ($auth->id != $window->nurse->user->id) {
            return;
        }

        $sentence = "Nurse $auth->fullName has just deleted the Window for ";
        $sentence .= "$window->dayName, {$window->date->format('m-d-Y')} from $window->window_time_start to $window->window_time_end. View Schedule at ";
        $sentence .= route('get.admin.nurse.schedules');

//        Slack::to('#callcenter_engagement')->send($sentence);
    }


}