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
//        $yesterdaysEvents = LoginLogout::orderBy('created_at', 'asc')->where([
//            ['created_at', '>=', Carbon::parse($date)->startOfDay()],
//            ['created_at', '<=', Carbon::parse($date)->endOfDay()],
//        ])->get();
//
//        $userIdsForYesterdayEvents = $yesterdaysEvents->unique('user_id')->pluck('user_id');
//        // 1.    Fix broken logouts
//        // 2.   Calculate
//
//        $eventsGroupedByUser = [];
//        foreach ($userIdsForYesterdayEvents as $id) {
//            $eventsGroupedByUser[$id] = $yesterdaysEvents->where('user_id', $id)->all();
//        }
    }
}
