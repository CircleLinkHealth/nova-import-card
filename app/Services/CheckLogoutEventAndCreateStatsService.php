<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\LoginLogout;
use Carbon\Carbon;

class CheckLogoutEventAndCreateStatsService
{
    public function checkLogoutEventsForDay(Carbon $date)
    {
        $x = LoginLogout::where([
            ['created_at', '>=', Carbon::parse($date)->startOfDay()],
            ['created_at', '<=', Carbon::parse($date)->endOfDay()],
        ])->where('event', '=', 'login')->select('user_id')->get();
    }
}
