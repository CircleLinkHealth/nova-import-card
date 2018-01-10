<?php

namespace Tests\Unit;

use App\Practice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PracticeTest extends TestCase
{
    use DatabaseTransactions;

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
            $this->assertFalse(str_contains($item, [' ']));
        }
    }
}
