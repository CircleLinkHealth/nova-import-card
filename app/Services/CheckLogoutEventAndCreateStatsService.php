<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\LoginLogout;
use Carbon\Carbon;

class CheckLogoutEventAndCreateStatsService
{
    public function checkLogoutEvent(Carbon $date)
    {
        $yesterdaysEvents = LoginLogout::with('activities')
            ->orderBy('created_at', 'asc')
            ->where([
                ['created_at', '>=', Carbon::parse($date)->startOfDay()],
                ['created_at', '<=', Carbon::parse($date)->endOfDay()],
            ])->get();

        foreach ($yesterdaysEvents as $event) {
            if (null === $event->logout_time) {
                $this->fixAndInsertLogoutEvent($event, $date);
            }
        }
    }

    /**
     * @param $event
     * @param Carbon $date
     */
    public function fixAndInsertLogoutEvent($event, Carbon $date)
    {
        $latestActivityAfterLogin = $event->activities->sortBy('end_time')->where('end_time', '>=', $event->login_time)
            ->where('end_time', '<=', Carbon::parse($event->login_time)->endOfDay())
            ->first();

        $endTime = Carbon::parse($latestActivityAfterLogin->end_time)->toDateTime();

        try {
            $event->logout_time = $endTime;
            $event->was_edited  = true;
            $event->save();
        } catch (\Exception $exception) {
            //@todo: maybe get next login time an set missing logout before that. If it is last login on table then set it to where??
            \Log::error(`Couldnt get end_time from page_timer table, for logout event $event->id`);
        }
    }
}
