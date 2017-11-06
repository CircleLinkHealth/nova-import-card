<?php

namespace Tests\unit;

use App\Call;
use Carbon\Carbon;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class NurseScheduledCallsTest extends TestCase
{
    use UserHelpers;

    private $nurse;
    private $patient;

    public function setUp()
    {
        parent::setUp();

        $this->nurse = $this->createUser(8, 'care-center');
        $this->patient = $this->createUser(8, 'participant');
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
            'scheduled_date' => Carbon::today(),
        ]);

        $call2 = Call::create([
            'status' => 'not reached',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => Carbon::yesterday(),
            'scheduled_date' => Carbon::today(),
        ]);

        $call3 = Call::create([
            'status' => 'scheduled',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => null,
            'scheduled_date' => Carbon::today(),
        ]);

        $call4 = Call::create([
            'status' => 'reached',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => Carbon::yesterday(),
            'scheduled_date' => Carbon::today(),
        ]);

        $call5 = Call::create([
            'status' => 'reached',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => Carbon::now(),
            'scheduled_date' => Carbon::today(),
        ]);

        $call6 = Call::create([
            'status' => 'dropped',
            'inbound_cpm_id' => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date' => null,
            'scheduled_date' => Carbon::today(),
        ]);

        $scheduledCallCount = $this->nurse->nurseInfo->countScheduledCallsForToday();
        $successfulCallCount = $this->nurse->nurseInfo->countSuccessfulCallsMadeToday();
        $completedCallCount = $this->nurse->nurseInfo->countCompletedCallsForToday();

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
        $calls = $this->nurse->nurseInfo->countScheduledCallsForToday();

        $this->assertEquals(0, $calls);
    }
}
