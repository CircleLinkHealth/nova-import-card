<?php

use App\Activity;
use App\PageTimer;
use App\User;

class PerformanceTest extends TestCase
{
    public function testExample()
    {
        $nurses = User::ofType('care-center')->all();

        foreach ($nurses as $nurse) {
            $activityTime = Activity::createdBy($nurse)
                ->createdToday()
                ->sum('duration');

            $systemTime = PageTimer::where('provider_id', $nurse->id)
                ->createdToday()
                ->sum('billable_duration');

            $performance = $activityTime / $systemTime;

            $totalTimeInSystemToday = secondsToHMS($systemTime);

            $totalTimeInSystemThisMonthInSeconds = PageTimer::where('provider_id', $nurse->id)
                ->createdThisMonth()
                ->sum('billable_duration');

            $totalTimeInSystemThisMonth = secondsToHMS($totalTimeInSystemThisMonthInSeconds);

            $totalEarningsThisMonth = $totalTimeInSystemThisMonthInSeconds * $nurse->nurseInfo->hourly_rate / 60 / 60;
        }
    }
}
