<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\LoginLogout;
use Carbon\Carbon;

class CalculateLoginLogoutActivityService
{
    public function calculateLoginLogoutActivity($date)
    {
        $yesterdaysEvents = LoginLogout::where([
            ['created_at', '>=', Carbon::parse($date)->startOfDay()],
            ['created_at', '<=', Carbon::parse($date)->endOfDay()],
        ])->get();

        foreach ($yesterdaysEvents as $event) {
            $loginTime  = Carbon::parse($event->login_time);
            $logoutTime = Carbon::parse($event->logout_time);

            $event->duration_in_sec = $logoutTime->diffInSeconds($loginTime);
            $event->save();
        }
    }
}
