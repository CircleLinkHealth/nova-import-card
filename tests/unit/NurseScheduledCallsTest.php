<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class NurseScheduledCallsTest extends TestCase
{
    use UserHelpers;

    private $nurse;
    private $patient;
    private $practice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->nurse    = $this->createUser($this->practice->id, 'care-center');
        $this->patient  = $this->createUser($this->practice->id, 'participant');
    }

    /**
     * Test it gets calls (scheduled, reached, not reached) for today.
     */
    public function test_daily_report()
    {
        $call1 = Call::create([
            'status'          => 'scheduled',
            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date'     => null,
            'scheduled_date'  => Carbon::today()->toDateString(),
        ]);

        $call2 = Call::create([
            'status'          => 'not reached',
            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date'     => Carbon::yesterday()->toDateTimeString(),
            'scheduled_date'  => Carbon::today()->toDateString(),
        ]);

        $call3 = Call::create([
            'status'          => 'scheduled',
            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date'     => null,
            'scheduled_date'  => Carbon::today()->toDateString(),
        ]);

        $call4 = Call::create([
            'status'          => 'reached',
            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date'     => Carbon::yesterday()->toDateTimeString(),
            'scheduled_date'  => Carbon::today()->toDateString(),
        ]);

        $call5 = Call::create([
            'status'          => 'reached',
            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date'     => Carbon::now()->toDateTimeString(),
            'scheduled_date'  => Carbon::today()->toDateString(),
        ]);

        $call6 = Call::create([
            'status'          => 'dropped',
            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,
            'called_date'     => null,
            'scheduled_date'  => Carbon::today()->toDateString(),
        ]);

        $scheduledCallCount  = $this->nurse->countScheduledCallsForToday();
        $successfulCallCount = $this->nurse->countSuccessfulCallsMadeToday();
        $completedCallCount  = $this->nurse->countCompletedCallsForToday();

        $this->assertEquals(4, $scheduledCallCount);
        $this->assertEquals(1, $successfulCallCount);
        $this->assertEquals(1, $completedCallCount);
    }

    /**
     * Test it returns 0 when there are no scheduled calls for today.
     */
    public function test_no_scheduled_calls_for_today()
    {
        $calls = $this->nurse->countScheduledCallsForToday();

        $this->assertEquals(0, $calls);
    }
}
