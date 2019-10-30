<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Reports\Sales\PracticeReportable;
use App\Reports\Sales\StatsHelper;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class PracticeStatsHelperTest extends TestCase
{
    use UserHelpers;
    protected $end;
    protected $nurse;
    protected $practice;
    protected $service;
    protected $start;

    public function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class, 1)->create()->first();

        $this->nurse = $this->createUser($this->practice->id, 'care-center');

        $this->end   = Carbon::now();
        $this->start = $this->end->copy()->subWeek(1);

        $this->service = new StatsHelper(new PracticeReportable($this->practice));

        for ($i = 0; $i < 10; ++$i) {
            $this->patients[$i] = $this->createUser($this->practice->id, 'participant');
        }
    }

    /**
     * Assert that it counts all calls.
     */
    public function test_call_count()
    {
        for ($i = 0; $i < 4; ++$i) {
            $this->patients[$i]->inboundCalls()->create([
                'note_id',
                'service'         => 'phone',
                'status'          => 'scheduled',
                'inbound_cpm_id'  => $this->patients[$i]->id,
                'called_date'     => $this->start,
                'is_cpm_outbound' => true,
            ]);
        }

        for ($i = 5; $i < 9; ++$i) {
            $this->patients[$i]->inboundCalls()->create([
                'note_id',
                'service'         => 'phone',
                'status'          => 'reached',
                'inbound_cpm_id'  => $this->patients[$i]->id,
                'called_date'     => $this->start,
                'is_cpm_outbound' => true,
            ]);
        }

        $this->assertEquals(8, $this->service->callCount($this->start, $this->end));
    }

    /**
     * Assert that it counts added, withdrawn, paused patients correctly.
     */
    public function test_enrollment_count()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->patients[$i]->setDateWithdrawn(Carbon::now());
        }

        for ($i = 4; $i < 8; ++$i) {
            $this->patients[$i]->setDatePaused(Carbon::now()->subDays(3));
        }

        $enrollmentCount = $this->service->enrollmentCount($this->start, $this->end);

        $this->assertEquals(10, $enrollmentCount['added']);
        $this->assertEquals(2, $enrollmentCount['withdrawn']);
        $this->assertEquals(4, $enrollmentCount['paused']);
    }

    /**
     * Assert that it counts all successful calls.
     */
    public function test_successful_call_count()
    {
        for ($i = 5; $i < 9; ++$i) {
            $this->patients[$i]->inboundCalls()->create([
                'note_id',
                'service'         => 'phone',
                'status'          => 'reached',
                'inbound_cpm_id'  => $this->patients[$i]->id,
                'called_date'     => $this->start,
                'is_cpm_outbound' => true,
            ]);
        }

        $this->assertEquals(4, $this->service->successfulCallCount($this->start, $this->end));
    }

    /**
     * Assert that it counts total CCM time.
     */
    public function test_total_ccm_time()
    {
        for ($i = 5; $i < 9; ++$i) {
            $this->patients[$i]->patientActivities()->create([
                'duration'     => 3600,
                'patient_id'   => $this->patients[$i]->id,
                'performed_at' => $this->start,
                'provider_id'  => $this->nurse->id,
            ]);
        }

        for ($i = 0; $i < 4; ++$i) {
            $this->patients[$i]->patientActivities()->create([
                'duration'     => 1800,
                'patient_id'   => $this->patients[$i]->id,
                'performed_at' => $this->end->copy()->subDay(),
                'provider_id'  => $this->nurse->id,
            ]);
        }

        $this->assertEquals(6, $this->service->totalCCMTimeHours($this->start, $this->end));
    }
}
