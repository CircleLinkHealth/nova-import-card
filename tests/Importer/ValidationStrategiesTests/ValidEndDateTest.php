<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Importer\ValidationStrategiesTests;

use Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/1/16
 * Time: 8:59 PM.
 */
class ValidEndDateTest extends TestCase
{
    public function getValidationStrategy()
    {
        return new \App\Importer\Section\Validators\ValidEndDate();
    }

    public function mockProblem($date)
    {
        $problem                   = new stdClass();
        @$problem->date_range->end = $date;

        return $problem;
    }

    public function test_future_date_returns_active()
    {
        $this->assertTrue($this->getValidationStrategy()->validate($this->mockProblem(\Carbon\Carbon::tomorrow())));
    }

    public function test_invalid_date_returns_inactive()
    {
        $this->assertFalse($this->getValidationStrategy()->validate($this->mockProblem('not a date')));
    }

    public function test_null_date_returns_inactive()
    {
        $this->assertFalse($this->getValidationStrategy()->validate($this->mockProblem(null)));
    }

    public function test_past_date_returns_inactive()
    {
        $this->assertFalse($this->getValidationStrategy()->validate($this->mockProblem('12/29/2015')));
    }
}
