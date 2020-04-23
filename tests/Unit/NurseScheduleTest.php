<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\TestCase;

class NurseScheduleTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;

    private $nurse;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->nurse = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->be($this->nurse);
    }

    public function test_empty_schedule()
    {
        $schedule = $this->nurse->nurseInfo->currentWeekWindows()->get();

        $this->assertTrue($schedule->isEmpty());
    }

    public function test_non_empty_schedule()
    {
        $this->nurse->nurseInfo->windows()->create([
            'date'              => Carbon::now()->format('Y-m-d'),
            'day_of_week'       => 2,
            'window_time_start' => '09:00',
            'window_time_end'   => '11:00',
        ]);

        $this->nurse->nurseInfo->windows()->create([
            'date'              => Carbon::now()->format('Y-m-d'),
            'day_of_week'       => 2,
            'window_time_start' => '13:00',
            'window_time_end'   => '22:00',
        ]);

        $this->nurse->nurseInfo->windows()->create([
            'date'              => Carbon::now()->format('Y-m-d'),
            'day_of_week'       => 1,
            'window_time_start' => '09:00',
            'window_time_end'   => '11:00',
        ]);

        $this->nurse->nurseInfo->windows()->create([
            'date'              => Carbon::now()->format('Y-m-d'),
            'day_of_week'       => 7,
            'window_time_start' => '13:00',
            'window_time_end'   => '22:00',
        ]);

        $schedule = $this->nurse->nurseInfo->currentWeekWindows()->get();

        $this->assertTrue($schedule->isNotEmpty());
    }
}
