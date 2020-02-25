<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Customer\Entities\Practice;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    private $practice;

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create([]);
    }

    public function test_report_recipients_attribute()
    {
        $this->practice->weekly_report_recipients = ' m@m.com,  a@g.k   ,    gg@h.as';

        $array = $this->practice->getWeeklyReportRecipientsArray();

        $this->assertCount(3, $array);

        foreach ($array as $item) {
            $this->assertFalse(Illuminate\Support\Str::contains($item, [' ']));
        }
    }
}
