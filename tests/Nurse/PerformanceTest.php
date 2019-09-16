<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Nurse;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    public function test_example()
    {
        $nurses = User::ofType('care-center')->get();

        foreach ($nurses as $nurse) {
            $activityTime = Activity::createdBy($nurse)
                ->createdToday()
                ->sum('duration');

            $systemTime = PageTimer::where('provider_id', $nurse->id)
                ->createdToday()
                ->sum('billable_duration');

            $performance = $activityTime / $systemTime;

            $totalTimeInSystemOnGivenDate = secondsToHMS($systemTime);

            $totalTimeInSystemThisMonthInSeconds = PageTimer::where('provider_id', $nurse->id)
                ->createdThisMonth()
                ->sum('billable_duration');

            $totalTimeInSystemThisMonth = secondsToHMS($totalTimeInSystemThisMonthInSeconds);

            $totalEarningsThisMonth = $totalTimeInSystemThisMonthInSeconds * $nurse->nurseInfo->hourly_rate / 60 / 60;
        }
    }
}
