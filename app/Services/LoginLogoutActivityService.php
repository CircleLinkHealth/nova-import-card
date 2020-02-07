<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\LoginLogout;
use Carbon\Carbon;

class LoginLogoutActivityService
{
    public static function yesterdaysActivity(Carbon $date)
    {
        return LoginLogout::with('activities', 'user')
            ->orderBy('created_at', 'asc')
            ->whereHas('user', function ($user) {
                $user->ofType('care-center');
            })
            ->where([
                ['created_at', '>=', Carbon::parse($date)->startOfDay()],
                ['created_at', '<=', Carbon::parse($date)->endOfDay()],
            ])->get();
    }
}
