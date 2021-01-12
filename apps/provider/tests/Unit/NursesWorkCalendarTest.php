<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\CpmAdmin\Http\Controllers\CareCenter\CareCenter\WorkScheduleController;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class NursesWorkCalendarTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_a_nurse_can_create_single_work_window()
    {
//        $date = \Carbon\Carbon::parse(now())->toDateString();
//        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
//        $workScheduleData = [
//            'date' => $date,
//            'day_of_week' =>$dayOfWeek,
//            'window_time_start' => '10:00',
//            'window_time_end' => '18:00',
//            'repeat_frequency' => 'does_not_repeat',
//            'until' => null,
//        ];
//
//        $window = $this->controller()->saveNurseSingleWindow($workScheduleData);
//        $this->assertTrue($window);
    }

    private function controller()
    {
//        return app(WorkScheduleController::class);
    }
}
