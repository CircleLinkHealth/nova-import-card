<?php

namespace Tests\unit;

use App\Call;
use App\Practice;
use Carbon\Carbon;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class NurseScheduledCallsTest extends TestCase
{
    use UserHelpers;

    private $nurse;
    private $patient;
    private $practice;

    public function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->nurse = $this->createUser($this->practice->id, 'care-center');
        $this->patient = $this->createUser($this->practice->id, 'participant');
    }

    /**
     * Test it gets calls (scheduled, reached, not reached) for today.
     *
     * @return void
     */
    public function testDailyReport()
    {
        $call1 = Call::create([
            'status' => 'scheduled',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => null,
            'scheduled_date' => Carbon::today()->toDateString(),
        ]);

        $call2 = Call::create([
            'status' => 'not reached',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => Carbon::yesterday()->toDateTimeString(),
            'scheduled_date' => Carbon::today()->toDateString(),
        ]);

        $call3 = Call::create([
            'status' => 'scheduled',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => null,
            'scheduled_date' => Carbon::today()->toDateString(),
        ]);

        $call4 = Call::create([
            'status' => 'reached',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => Carbon::yesterday()->toDateTimeString(),
            'scheduled_date' => Carbon::today()->toDateString(),
        ]);

        $call5 = Call::create([
            'status' => 'reached',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => Carbon::now()->toDateTimeString(),
            'scheduled_date' => Carbon::today()->toDateString(),
        ]);

        $call6 = Call::create([
            'status' => 'dropped',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => null,
            'scheduled_date' => Carbon::today()->toDateString(),
        ]);

        $scheduledCallCount = $this->nurse->countScheduledCallsForToday();
        $successfulCallCount = $this->nurse->countSuccessfulCallsMadeToday();
        $completedCallCount = $this->nurse->countCompletedCallsForToday();

        $this->assertEquals(4, $scheduledCallCount);
        $this->assertEquals(1, $successfulCallCount);
        $this->assertEquals(1, $completedCallCount);
    }

    /**
     * Test it returns 0 when there are no scheduled calls for today.
     *
     * @return void
     */
    public function testNoScheduledCallsForToday()
    {
        $calls = $this->nurse->countScheduledCallsForToday();

        $this->assertEquals(0, $calls);
    }
}
