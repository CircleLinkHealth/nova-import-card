<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use Tests\TestCase;

class ParseCallDayAndTimeTest extends TestCase
{
    public function test_it_parses_day_range()
    {
        $result = parseCallDays('Monday-Friday');
        $this->assertEquals([
            1,
            2,
            3,
            4,
            5,
        ], $result);
    }
}
