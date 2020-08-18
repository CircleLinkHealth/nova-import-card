<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\Rules;

use App\Rules\DateValidatorMultipleFormats;
use Tests\TestCase;

class DateValidatorMultipleFormatsTest extends TestCase
{
    public function test_it_fails_invalid_single_date_format()
    {
        self::assertFalse($this->validator(['H:i'])->passes('date', '10:00:00'));
    }

    public function test_it_fails_invalid_value_if_it_matches_one_of_multiple_date_formats()
    {
        self::assertFalse($this->validator(['H:i', 'H:i:s'])->passes('date', '2020-01-01 10:00:00'));
        self::assertFalse($this->validator(['m-d-Y', 'H:i:s'])->passes('date', '2020-01-01 10:00:00'));
    }

    public function test_it_passes_valid_single_date_format()
    {
        self::assertTrue($this->validator(['H:i'])->passes('date', '10:00'));
    }

    public function test_it_passes_valid_value_if_it_matches_one_of_multiple_date_formats()
    {
        self::assertTrue($this->validator(['H:i', 'H:i:s'])->passes('date', '10:00'));
        self::assertTrue($this->validator(['H:i', 'H:i:s'])->passes('date', '10:00:00'));
    }

    private function validator(array $formats)
    {
        return new DateValidatorMultipleFormats($formats);
    }
}
