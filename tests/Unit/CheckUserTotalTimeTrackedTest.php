<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use App\UserTotalTimeChecker;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\TestCase;

class CheckUserTotalTimeTrackedTest extends TestCase
{
    use TimeHelpers;
    use UserHelpers;

    public function test_it_does_not_raise_alert_for_total_time_in_last_7_days()
    {
        $practice = factory(Practice::class)->create();
        $nurse    = $this->createUser($practice->id, 'care-center');

        $hoursPerDay = 4;

        //33.6
        $maxAllowed = 7 * $hoursPerDay * UserTotalTimeChecker::getThresholdForWeek();

        $start = now()->subDay(7);
        $sub6  = now()->subDay(6);
        $sub5  = now()->subDay(5);
        $sub4  = now()->subDay(4);
        $sub3  = now()->subDay(3);
        $sub2  = now()->subDay(2);
        $end   = now()->subDay(1);

        //7 days -> 28 hours committed
        $this->addWorkHours($nurse, $start, $hoursPerDay);
        $this->addWorkHours($nurse, $sub6, $hoursPerDay);
        $this->addWorkHours($nurse, $sub5, $hoursPerDay);
        $this->addWorkHours($nurse, $sub4, $hoursPerDay);
        $this->addWorkHours($nurse, $sub3, $hoursPerDay);
        $this->addWorkHours($nurse, $sub2, $hoursPerDay);
        $this->addWorkHours($nurse, $end, $hoursPerDay);

        //32 hours CPM time - Not above max (33.6)
        $this->addTime($nurse, null, 240, false, false, false, $start);
        $this->addTime($nurse, null, 240, false, false, false, $sub6);
        $this->addTime($nurse, null, 240, false, false, false, $sub5);
        $this->addTime($nurse, null, 240, false, false, false, $sub4);
        $this->addTime($nurse, null, 240, false, false, false, $sub3);
        $this->addTime($nurse, null, 240, false, false, false, $sub2);
        $this->addTime($nurse, null, 480, false, false, false, $end);

        $checker = new UserTotalTimeChecker($start, $end, true, $nurse->id);
        $alerts  = $checker->check();
        $this->assertFalse($alerts->has('daily'));
        $this->assertFalse($alerts->has('daily_committed'));
//        $this->assertFalse($alerts->has('weekly'));
    }

    public function test_it_does_not_raise_alert_for_total_time_less_than_8_hours_in_a_day()
    {
        $practice = factory(Practice::class)->create();
        $nurse    = $this->createUser($practice->id, 'care-center');

        $this->addTime($nurse, null, 120, false, false);
        $this->addTime($nurse, null, 120, false, false);
        $this->addTime($nurse, null, 120, false, false);
        $this->addTime($nurse, null, 100, false, false);

        $start = now()->startOfDay();
        $end   = now()->endOfDay();

        $checker = new UserTotalTimeChecker($start, $end, false, $nurse->id);
        $alerts  = $checker->check();
        $this->assertFalse($alerts->has('daily'));
        $this->assertFalse($alerts->has('daily_committed'));
    }

    public function test_it_raises_alert_for_total_time_over_8_hours_and_spanning_in_2_days()
    {
        $practice = factory(Practice::class)->create();
        $nurse    = $this->createUser($practice->id, 'care-center');

        $twelveHoursInMinutes = 12 * 60;
        Carbon::setTestNow(now()->setHour(16));
        $startTime = now()->subDay()->endOfDay()->subHours(6);

        $this->addTime($nurse, null, $twelveHoursInMinutes, false, false, false, $startTime);
        $this->addTime($nurse, null, 60, false, false, false);

        $start = now()->startOfDay();
        $end   = now()->endOfDay();

        $checker = new UserTotalTimeChecker($start, $end, false, $nurse->id);
        $alerts  = $checker->check();
        $this->assertTrue($alerts->has('daily'));
        $this->assertTrue($alerts->get('daily')->has("{$nurse->id}_{$nurse->display_name}"));
        $this->assertTrue($alerts->get('daily_committed')->has("{$nurse->id}_{$nurse->display_name}"));
    }

    public function test_it_raises_alert_for_total_time_over_8_hours_in_a_day()
    {
        $practice = factory(Practice::class)->create();
        $nurse    = $this->createUser($practice->id, 'care-center');

        Carbon::setTestNow(now()->setHour(11));
        $this->addTime($nurse, null, 120, false, false);
        $this->addTime($nurse, null, 120, false, false);
        $this->addTime($nurse, null, 120, false, false);
        $this->addTime($nurse, null, 120, false, false);
        $this->addTime($nurse, null, 30, false, false);

        $start = now()->startOfDay();
        $end   = now()->endOfDay();

        $checker = new UserTotalTimeChecker($start, $end, false, $nurse->id);
        $alerts  = $checker->check();
        $this->assertTrue($alerts->has('daily'));
        $this->assertTrue($alerts->get('daily')->has("{$nurse->id}_{$nurse->display_name}"));
        $this->assertTrue($alerts->get('daily_committed')->has("{$nurse->id}_{$nurse->display_name}"));
    }

    public function test_it_raises_alert_for_total_time_over_max_in_last_7_days()
    {
        $practice = factory(Practice::class)->create();
        $nurse    = $this->createUser($practice->id, 'care-center');

        $hoursPerDay = 4;

        //33.6
        $maxAllowed = 7 * $hoursPerDay * UserTotalTimeChecker::getThresholdForWeek();

        $start = now()->subDay(7);
        $sub6  = now()->subDay(6);
        $sub5  = now()->subDay(5);
        $sub4  = now()->subDay(4);
        $sub3  = now()->subDay(3);
        $sub2  = now()->subDay(2);
        $end   = now()->subDay(1);

        //7 days -> 28 hours committed
        $this->addWorkHours($nurse, $start, $hoursPerDay);
        $this->addWorkHours($nurse, $sub6, $hoursPerDay);
        $this->addWorkHours($nurse, $sub5, $hoursPerDay);
        $this->addWorkHours($nurse, $sub4, $hoursPerDay);
        $this->addWorkHours($nurse, $sub3, $hoursPerDay);
        $this->addWorkHours($nurse, $sub2, $hoursPerDay);
        $this->addWorkHours($nurse, $end, $hoursPerDay);

        //35 hours CPM time
        $this->addTime($nurse, null, 240, false, false, false, $start);
        $this->addTime($nurse, null, 240, false, false, false, $sub6);
        $this->addTime($nurse, null, 240, false, false, false, $sub5);
        $this->addTime($nurse, null, 480, false, false, false, $sub4);
        $this->addTime($nurse, null, 240, false, false, false, $sub3);
        $this->addTime($nurse, null, 240, false, false, false, $sub2);
        $this->addTime($nurse, null, 480, false, false, false, $end);

        $checker = new UserTotalTimeChecker($start, $end, true, $nurse->id);
        $alerts  = $checker->check();
        $this->assertFalse($alerts->has('daily'));
        $this->assertTrue($alerts->has('weekly'));
        $this->assertTrue($alerts->get('weekly')->has("{$nurse->id}_{$nurse->display_name}"));
        $this->assertTrue($alerts->get('weekly_committed')->has("{$nurse->id}_{$nurse->display_name}"));
        $time = $alerts->get('weekly')->get("{$nurse->id}_{$nurse->display_name}");
        $this->assertTrue($time > $maxAllowed);
    }
}
