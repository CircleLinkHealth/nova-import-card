<?php

namespace App\Observers;


use App\Activity;
use App\NurseContactWindow;
use App\PageTimer;

class PageTimerObserver
{
    /**
     * Listen for the NurseContactWindow created event.
     *
     * @param NurseContactWindow $window
     *
     * @internal param User $user
     */
    public function saved(PageTimer $pageTimer)
    {
        if ($pageTimer->billable_duration == 0) {
            Activity::where('page_timer_id', '=', $pageTimer->id)
                ->delete();
        } else {
            Activity::where('page_timer_id', '=', $pageTimer->id)
                ->update([
                    'duration' => $pageTimer->billable_duration,
                ]);
        }
    }


}