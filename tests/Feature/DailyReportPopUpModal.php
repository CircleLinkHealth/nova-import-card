<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Services\NurseCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\Helpers\CustomerTestCaseHelper;
use Tests\TestCase;

class DailyReportPopUpModal extends TestCase
{
    use CustomerTestCaseHelper;

    public function test_it_caches_data_for_a_day()
    {
        $nurse     = $this->createUsersOfType('care-center');
        $date      = Carbon::yesterday();
        $cacheTime = Carbon::now()->endOfDay();
        $cacheKey  = "daily-report-for-{$nurse->id}-{$date->toDateString()}";
        Cache::put($cacheKey, "Daily Report $nurse->id", $cacheTime);
//        Fake today date.
        Carbon::setTestNow($cacheTime->addDays(1));
        self::assertFalse(Cache::has($cacheKey));
    }

    public function test_it_caches_data_for_pop_up_modal()
    { // Only shows pop up once / day
        $nurse     = $this->createUsersOfType('care-center');
        $date      = Carbon::yesterday();
        $cacheTime = Carbon::now()->endOfDay();
        $cacheKey  = "daily-report-for-{$nurse->id}-{$date->toDateString()}";
        Cache::put($cacheKey, "Daily Report $nurse->id", $cacheTime);

        self::assertTrue(Cache::has($cacheKey));
    }

    public function test_it_will_fetch_report_data()
    {
        $nurse    = $this->createUsersOfType('care-center');
        $date     = Carbon::yesterday();
        $cacheKey = "daily-report-for-{$nurse->id}-{$date->toDateString()}";
        Auth::login($nurse);
        Artisan::call("create:dailyReportFakeData $nurse->id");
        $reportDataForCalendar = (new NurseCalendarService())->nurseDailyReportForDate($nurse->id, $date, $cacheKey);
        self::assertTrue( ! empty($reportDataForCalendar->first()));
    }
}
